<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmailCampaign;
use App\Models\Campaign;
use App\Models\CampaignCustomer;
use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Services\AuditService;
use Illuminate\Http\Request;

class EmailCampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailCampaign::with(['campaign', 'couponBatch', 'createdBy'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->campaign_id, fn($q) => $q->where('campaign_id', $request->campaign_id));

        $emailCampaigns = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'      => EmailCampaign::count(),
            'draft'      => EmailCampaign::where('status', 'draft')->count(),
            'sending'    => EmailCampaign::where('status', 'sending')->count(),
            'sent'       => EmailCampaign::where('status', 'sent')->count(),
            'failed'     => EmailCampaign::whereIn('status', ['failed', 'cancelled'])->count(),
            'scheduled'  => EmailCampaign::where('status', 'scheduled')->count(),
            'recipients' => EmailCampaign::sum('total_recipients'),
            'sent_total' => EmailCampaign::sum('sent_count'),
        ];

        $campaigns = Campaign::whereIn('status', ['active', 'paused'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.email-campaigns.index', compact('emailCampaigns', 'stats', 'campaigns'));
    }

    public function create()
    {
        $campaigns = Campaign::withCount('campaignCustomers')
            ->whereIn('status', ['active', 'paused', 'draft'])
            ->having('campaign_customers_count', '>', 0)
            ->orderBy('name')
            ->get();

        $campaignData = $campaigns->mapWithKeys(function ($campaign) {
            $batches = $campaign->couponBatches()
                ->whereIn('status', ['active', 'paused'])
                ->get(['id', 'name', 'status', 'code_type', 'general_code', 'discount_type', 'discount_value', 'prefix'])
                ->map(fn($b) => [
                    'id'             => $b->id,
                    'name'           => $b->name,
                    'code_type'      => $b->code_type,
                    'general_code'   => $b->general_code,
                    'discount_type'  => $b->discount_type,
                    'discount_value' => $b->discount_value,
                    'prefix'         => $b->prefix,
                    'batch_status'   => $b->status,
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

        $defaultFrom = config('services.email.zenvia_from_address', '');
        $defaultFromName = config('services.email.zenvia_from_name', 'CuponesHub');

        return view('admin.email-campaigns.create', compact('campaigns', 'campaignData', 'defaultFrom', 'defaultFromName'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'campaign_id'      => 'required|exists:campaigns,id',
            'coupon_batch_id'  => 'nullable|exists:coupon_batches,id',
            'subject'          => 'required|string|max:255',
            'from_name'        => 'required|string|max:100',
            'from_email'       => 'required|email|max:150',
            'message_template' => 'required|string',
            'scheduled_at'     => 'nullable|date|after:now',
        ]);

        $campaign  = Campaign::findOrFail($data['campaign_id']);
        $customers = $campaign->customers()
            ->where('customers.status', 'active')
            ->whereNotNull('customers.email')
            ->get(['customers.id', 'customers.email', 'customers.name']);

        $data['created_by_user_id'] = auth()->id();
        $data['status']             = $data['scheduled_at'] ? 'scheduled' : 'draft';
        $data['total_recipients']   = $customers->count();

        $emailCampaign = EmailCampaign::create($data);

        foreach ($customers->chunk(500) as $chunk) {
            $records = $chunk->map(fn($c) => [
                'email_campaign_id' => $emailCampaign->id,
                'customer_id'       => $c->id,
                'email'             => $c->email,
                'status'            => 'pending',
                'created_at'        => now(),
            ])->toArray();
            EmailRecipient::insert($records);
        }

        AuditService::log('created', EmailCampaign::class, $emailCampaign->id, [], $emailCampaign->toArray());

        return redirect()->route('admin.email-campaigns.show', $emailCampaign)
            ->with('success', "Campaña de email creada con {$customers->count()} destinatarios.");
    }

    public function show(EmailCampaign $emailCampaign)
    {
        $emailCampaign->load(['campaign', 'couponBatch', 'createdBy']);

        $recipientStats = [
            'pending' => $emailCampaign->recipients()->where('status', 'pending')->count(),
            'sent'    => $emailCampaign->recipients()->where('status', 'sent')->count(),
            'failed'  => $emailCampaign->recipients()->where('status', 'failed')->count(),
            'total'   => $emailCampaign->total_recipients,
        ];

        $missingCount = $emailCampaign->campaign_id
            ? CampaignCustomer::where('campaign_id', $emailCampaign->campaign_id)
                ->join('customers', 'customers.id', '=', 'campaign_customers.customer_id')
                ->whereNotNull('customers.email')
                ->whereNotIn('campaign_customers.customer_id',
                    EmailRecipient::where('email_campaign_id', $emailCampaign->id)->select('customer_id')
                )
                ->count()
            : 0;

        $recipients = $emailCampaign->recipients()
            ->with('customer')
            ->orderByRaw("FIELD(status, 'failed', 'pending', 'sent')")
            ->paginate(30);

        $availableBatches = $emailCampaign->campaign_id
            ? \App\Models\CouponBatch::where('campaign_id', $emailCampaign->campaign_id)
                ->whereIn('status', ['active', 'paused'])
                ->orderBy('name')
                ->get(['id', 'name', 'status', 'code_type', 'general_code', 'prefix', 'discount_type', 'discount_value'])
            : collect();

        return view('admin.email-campaigns.show', compact(
            'emailCampaign', 'recipients', 'recipientStats', 'missingCount', 'availableBatches'
        ));
    }

    public function send(EmailCampaign $emailCampaign)
    {
        if (!in_array($emailCampaign->status, ['draft', 'scheduled', 'failed'])) {
            return back()->with('error', 'La campaña no se puede enviar en este estado.');
        }
        if ($emailCampaign->total_recipients === 0) {
            return back()->with('error', 'La campaña no tiene destinatarios.');
        }

        ProcessEmailCampaign::dispatch($emailCampaign);
        $emailCampaign->update(['status' => 'sending', 'started_at' => now()]);

        AuditService::log('sent', EmailCampaign::class, $emailCampaign->id, [], ['status' => 'sending']);

        return back()->with('success', 'Campaña de email despachada a la cola de envío.');
    }

    public function cancel(EmailCampaign $emailCampaign)
    {
        if (!in_array($emailCampaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Solo se pueden cancelar campañas en estado borrador o programada.');
        }

        $emailCampaign->update(['status' => 'cancelled']);
        AuditService::log('cancelled', EmailCampaign::class, $emailCampaign->id, [], ['status' => 'cancelled']);

        return back()->with('success', 'Campaña cancelada.');
    }

    public function retry(EmailCampaign $emailCampaign)
    {
        $reset = $emailCampaign->recipients()
            ->where('status', 'failed')
            ->update(['status' => 'pending', 'error_message' => null, 'provider_response' => null]);

        if ($reset === 0) {
            return back()->with('error', 'No hay destinatarios fallidos para reintentar.');
        }

        ProcessEmailCampaign::dispatch($emailCampaign);
        $emailCampaign->update(['status' => 'sending', 'started_at' => now(), 'finished_at' => null]);

        AuditService::log('retried', EmailCampaign::class, $emailCampaign->id, [], [
            'reset_recipients' => $reset,
        ]);

        return back()->with('success', "Reintentando envío para {$reset} destinatarios fallidos.");
    }

    public function processPending(EmailCampaign $emailCampaign)
    {
        $pendingCount = $emailCampaign->recipients()->where('status', 'pending')->count();

        if ($pendingCount === 0) {
            return back()->with('error', 'No hay destinatarios pendientes de envío.');
        }

        ProcessEmailCampaign::dispatch($emailCampaign);
        $emailCampaign->update(['status' => 'sending', 'finished_at' => null]);

        AuditService::log('process_pending_dispatched', EmailCampaign::class, $emailCampaign->id, [], [
            'pending' => $pendingCount,
        ]);

        return back()->with('success', "{$pendingCount} destinatario(s) pendiente(s) despachados a la cola de envío.");
    }

    public function syncRecipients(EmailCampaign $emailCampaign)
    {
        if (!$emailCampaign->campaign_id) {
            return back()->with('error', 'Esta campaña de email no tiene una campaña vinculada.');
        }

        $missing = CampaignCustomer::where('campaign_id', $emailCampaign->campaign_id)
            ->join('customers', 'customers.id', '=', 'campaign_customers.customer_id')
            ->whereNotNull('customers.email')
            ->whereNotIn('campaign_customers.customer_id',
                EmailRecipient::where('email_campaign_id', $emailCampaign->id)->select('customer_id')
            )
            ->select('customers.id', 'customers.email')
            ->get();

        if ($missing->isEmpty()) {
            return back()->with('info', 'Todos los clientes de la campaña ya están incluidos como destinatarios.');
        }

        $rows = $missing->map(fn($c) => [
            'email_campaign_id' => $emailCampaign->id,
            'customer_id'       => $c->id,
            'email'             => $c->email,
            'status'            => 'pending',
            'created_at'        => now(),
        ])->all();

        EmailRecipient::insert($rows);
        $emailCampaign->increment('total_recipients', count($rows));

        $shouldDispatch = in_array($emailCampaign->status, ['sending', 'sent', 'failed']);
        if ($shouldDispatch) {
            ProcessEmailCampaign::dispatch($emailCampaign);
            $emailCampaign->update(['status' => 'sending', 'finished_at' => null]);
        }

        AuditService::log('recipients_synced', EmailCampaign::class, $emailCampaign->id, [], [
            'added'      => count($rows),
            'dispatched' => $shouldDispatch,
        ]);

        $msg = count($rows) . ' clientes nuevos añadidos como destinatarios.';
        $msg .= $shouldDispatch
            ? ' El envío fue despachado a la cola automáticamente.'
            : ' Usa "Enviar ahora" cuando estés listo para enviar.';

        return back()->with('success', $msg);
    }

    public function linkBatch(Request $request, EmailCampaign $emailCampaign)
    {
        $data = $request->validate([
            'coupon_batch_id' => 'nullable|exists:coupon_batches,id',
        ]);

        $old = $emailCampaign->coupon_batch_id;
        $emailCampaign->update(['coupon_batch_id' => $data['coupon_batch_id'] ?? null]);

        AuditService::log('batch_linked', EmailCampaign::class, $emailCampaign->id,
            ['coupon_batch_id' => $old],
            ['coupon_batch_id' => $emailCampaign->coupon_batch_id]
        );

        return back()->with('success', $emailCampaign->coupon_batch_id
            ? 'Lote de cupones vinculado correctamente.'
            : 'Lote de cupones desvinculado.');
    }
}
