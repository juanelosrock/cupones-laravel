<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSmsCampaign;
use App\Models\Campaign;
use App\Models\CampaignCustomer;
use App\Models\CouponBatch;
use App\Models\SmsCampaign;
use App\Models\SmsRecipient;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SmsCampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = SmsCampaign::with(['campaign', 'couponBatch', 'createdBy'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->campaign_id, fn($q) => $q->where('campaign_id', $request->campaign_id));

        $smsCampaigns = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'      => SmsCampaign::count(),
            'draft'      => SmsCampaign::where('status', 'draft')->count(),
            'sending'    => SmsCampaign::where('status', 'sending')->count(),
            'sent'       => SmsCampaign::where('status', 'sent')->count(),
            'failed'     => SmsCampaign::whereIn('status', ['failed', 'cancelled'])->count(),
            'scheduled'  => SmsCampaign::where('status', 'scheduled')->count(),
            'recipients' => SmsCampaign::sum('total_recipients'),
            'sent_total' => SmsCampaign::sum('sent_count'),
        ];

        $campaigns = Campaign::whereIn('status', ['active', 'paused'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.sms-campaigns.index', compact('smsCampaigns', 'stats', 'campaigns'));
    }

    public function create()
    {
        // Load campaigns that have customers
        $campaigns = Campaign::withCount('campaignCustomers')
            ->whereIn('status', ['active', 'paused', 'draft'])
            ->having('campaign_customers_count', '>', 0)
            ->orderBy('name')
            ->get();

        // Build campaign data for Alpine.js (customers + batches per campaign)
        $campaignData = $campaigns->mapWithKeys(function ($campaign) {
            $batches = $campaign->couponBatches()
                ->where('status', 'active')
                ->get(['id', 'name', 'code_type', 'general_code', 'discount_type', 'discount_value', 'prefix'])
                ->map(fn($b) => [
                    'id'             => $b->id,
                    'name'           => $b->name,
                    'code_type'      => $b->code_type,
                    'general_code'   => $b->general_code,
                    'discount_type'  => $b->discount_type,
                    'discount_value' => $b->discount_value,
                    'prefix'         => $b->prefix,
                    'label'          => $b->code_type === 'general'
                        ? "{$b->name} (código: {$b->general_code})"
                        : "{$b->name} (códigos únicos, prefijo: {$b->prefix})",
                ])
                ->values();

            return [$campaign->id => [
                'customer_count' => $campaign->campaign_customers_count,
                'batches'        => $batches,
            ]];
        });

        $landingConfigs = \App\Models\LandingPageConfig::orderBy('name')->get(['id', 'name', 'template', 'is_default']);

        return view('admin.sms-campaigns.create', compact('campaigns', 'campaignData', 'landingConfigs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:150',
            'campaign_id'        => 'required|exists:campaigns,id',
            'coupon_batch_id'    => 'nullable|exists:coupon_batches,id',
            'landing_config_id'  => 'nullable|exists:landing_page_configs,id',
            'send_consent_link'  => 'nullable|boolean',
            'message_template'   => 'required|string|max:160',
            'scheduled_at'       => 'nullable|date|after:now',
        ]);

        $data['send_consent_link'] = $request->boolean('send_consent_link');

        $campaign = Campaign::findOrFail($data['campaign_id']);

        // Get customers from campaign_customers
        $customers = $campaign->customers()
            ->where('customers.status', 'active')
            ->get(['customers.id', 'customers.phone', 'customers.name']);

        $data['created_by_user_id'] = auth()->id();
        $data['status']             = $data['scheduled_at'] ? 'scheduled' : 'draft';
        $data['total_recipients']   = $customers->count();

        $smsCampaign = SmsCampaign::create($data);

        $needsToken = $data['send_consent_link'];

        // Create recipient records in chunks
        foreach ($customers->chunk(500) as $chunk) {
            $records = $chunk->map(fn($c) => [
                'sms_campaign_id' => $smsCampaign->id,
                'customer_id'     => $c->id,
                'phone'           => $c->phone,
                'status'          => 'pending',
                'consent_token'   => $needsToken ? Str::random(48) : null,
                'created_at'      => now(),
            ])->toArray();
            SmsRecipient::insert($records);
        }

        AuditService::log('created', SmsCampaign::class, $smsCampaign->id, [], $smsCampaign->toArray());

        return redirect()->route('admin.sms-campaigns.show', $smsCampaign)
            ->with('success', "Campaña SMS creada con {$customers->count()} destinatarios.");
    }

    public function show(SmsCampaign $smsCampaign)
    {
        $smsCampaign->load(['campaign', 'couponBatch', 'createdBy']);

        $recipientStats = [
            'pending'          => $smsCampaign->recipients()->where('status', 'pending')->count(),
            'sent'             => $smsCampaign->recipients()->where('status', 'sent')->count(),
            'failed'           => $smsCampaign->recipients()->where('status', 'failed')->count(),
            'total'            => $smsCampaign->total_recipients,
            'consent_accepted' => $smsCampaign->send_consent_link
                ? $smsCampaign->recipients()->whereNotNull('consent_accepted_at')->count()
                : null,
            'consent_pending'  => $smsCampaign->send_consent_link
                ? $smsCampaign->recipients()->where('status', 'sent')->whereNull('consent_accepted_at')->count()
                : null,
        ];

        // Clientes de la campaña padre que aún no son destinatarios de esta SMS campaign
        $missingCount = $smsCampaign->campaign_id
            ? CampaignCustomer::where('campaign_id', $smsCampaign->campaign_id)
                ->join('customers', 'customers.id', '=', 'campaign_customers.customer_id')
                ->whereNotNull('customers.phone')
                ->whereNotIn('campaign_customers.customer_id',
                    SmsRecipient::where('sms_campaign_id', $smsCampaign->id)->select('customer_id')
                )
                ->count()
            : 0;

        $recipients = $smsCampaign->recipients()
            ->with('customer')
            ->orderByRaw("FIELD(status, 'failed', 'pending', 'sent')")
            ->paginate(30);

        return view('admin.sms-campaigns.show', compact('smsCampaign', 'recipients', 'recipientStats', 'missingCount'));
    }

    public function syncRecipients(SmsCampaign $smsCampaign)
    {
        if (!$smsCampaign->campaign_id) {
            return back()->with('error', 'Esta campaña SMS no tiene una campaña vinculada.');
        }

        $missing = CampaignCustomer::where('campaign_id', $smsCampaign->campaign_id)
            ->join('customers', 'customers.id', '=', 'campaign_customers.customer_id')
            ->whereNotNull('customers.phone')
            ->whereNotIn('campaign_customers.customer_id',
                SmsRecipient::where('sms_campaign_id', $smsCampaign->id)->select('customer_id')
            )
            ->select('customers.id', 'customers.phone')
            ->get();

        if ($missing->isEmpty()) {
            return back()->with('info', 'Todos los clientes de la campaña ya están incluidos como destinatarios.');
        }

        $rows = $missing->map(fn($c) => [
            'sms_campaign_id' => $smsCampaign->id,
            'customer_id'     => $c->id,
            'phone'           => $c->phone,
            'consent_token'   => $smsCampaign->send_consent_link ? (string) Str::uuid() : null,
            'status'          => 'pending',
            'created_at'      => now(),
        ])->all();

        SmsRecipient::insert($rows);
        $smsCampaign->increment('total_recipients', count($rows));

        // Si la campaña ya fue enviada o está enviando, despachar el job para procesar los nuevos pendientes
        $shouldDispatch = in_array($smsCampaign->status, ['sending', 'sent', 'failed']);
        if ($shouldDispatch) {
            ProcessSmsCampaign::dispatch($smsCampaign);
            $smsCampaign->update(['status' => 'sending', 'finished_at' => null]);
        }

        AuditService::log('recipients_synced', SmsCampaign::class, $smsCampaign->id, [], [
            'added'      => count($rows),
            'dispatched' => $shouldDispatch,
        ]);

        $msg = count($rows) . ' clientes nuevos añadidos como destinatarios.';
        $msg .= $shouldDispatch
            ? ' El envío fue despachado a la cola automáticamente.'
            : ' Usa "Enviar ahora" cuando estés listo para enviar.';

        return back()->with('success', $msg);
    }

    public function send(SmsCampaign $smsCampaign)
    {
        if (!in_array($smsCampaign->status, ['draft', 'scheduled', 'failed'])) {
            return back()->with('error', 'La campaña no se puede enviar en este estado.');
        }

        if ($smsCampaign->total_recipients === 0) {
            return back()->with('error', 'La campaña no tiene destinatarios.');
        }

        ProcessSmsCampaign::dispatch($smsCampaign);
        $smsCampaign->update(['status' => 'sending', 'started_at' => now()]);

        AuditService::log('sent', SmsCampaign::class, $smsCampaign->id, [], ['status' => 'sending']);

        return back()->with('success', 'Campaña SMS despachada a la cola de envío.');
    }

    public function cancel(SmsCampaign $smsCampaign)
    {
        if (!in_array($smsCampaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Solo se pueden cancelar campañas en estado borrador o programada.');
        }

        $smsCampaign->update(['status' => 'cancelled']);
        AuditService::log('cancelled', SmsCampaign::class, $smsCampaign->id, [], ['status' => 'cancelled']);

        return back()->with('success', 'Campaña cancelada.');
    }

    public function retry(SmsCampaign $smsCampaign)
    {
        // Reset failed recipients back to pending
        $reset = $smsCampaign->recipients()
            ->where('status', 'failed')
            ->update([
                'status'           => 'pending',
                'error_message'    => null,
                'provider_response'=> null,
            ]);

        if ($reset === 0) {
            return back()->with('error', 'No hay destinatarios fallidos para reintentar.');
        }

        // Re-dispatch job
        ProcessSmsCampaign::dispatch($smsCampaign);
        $smsCampaign->update(['status' => 'sending', 'started_at' => now(), 'finished_at' => null]);

        AuditService::log('retried', SmsCampaign::class, $smsCampaign->id, [], [
            'reset_recipients' => $reset,
            'status' => 'sending',
        ]);

        return back()->with('success', "Reintentando envío para {$reset} destinatarios fallidos.");
    }

    public function retryRecipient(SmsCampaign $smsCampaign, SmsRecipient $recipient)
    {
        if ($recipient->sms_campaign_id !== $smsCampaign->id) {
            abort(403);
        }
        if ($recipient->status !== 'failed') {
            return back()->with('error', 'Solo se pueden reintentar destinatarios fallidos.');
        }

        $recipient->update([
            'status'            => 'pending',
            'error_message'     => null,
            'provider_response' => null,
        ]);

        // Dispatch job to process only pending recipients
        ProcessSmsCampaign::dispatch($smsCampaign);
        $smsCampaign->update(['status' => 'sending', 'finished_at' => null]);

        return back()->with('success', "Reintentando envío a {$recipient->phone}.");
    }
}
