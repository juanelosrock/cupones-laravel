<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsAppCampaign;
use App\Models\Campaign;
use App\Models\CampaignCustomer;
use App\Models\CouponBatch;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppRecipient;
use App\Services\AuditService;
use Illuminate\Http\Request;

class WhatsAppCampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsAppCampaign::with(['campaign', 'couponBatch', 'createdBy'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->campaign_id, fn($q) => $q->where('campaign_id', $request->campaign_id));

        $waCampaigns = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'      => WhatsAppCampaign::count(),
            'draft'      => WhatsAppCampaign::where('status', 'draft')->count(),
            'sending'    => WhatsAppCampaign::where('status', 'sending')->count(),
            'sent'       => WhatsAppCampaign::where('status', 'sent')->count(),
            'failed'     => WhatsAppCampaign::whereIn('status', ['failed', 'cancelled'])->count(),
            'scheduled'  => WhatsAppCampaign::where('status', 'scheduled')->count(),
            'recipients' => WhatsAppCampaign::sum('total_recipients'),
            'sent_total' => WhatsAppCampaign::sum('sent_count'),
        ];

        $campaigns = Campaign::whereIn('status', ['active', 'paused'])
            ->orderBy('name')->get(['id', 'name']);

        return view('admin.whatsapp-campaigns.index', compact('waCampaigns', 'stats', 'campaigns'));
    }

    public function create()
    {
        $campaigns = Campaign::withCount('campaignCustomers')
            ->whereIn('status', ['active', 'paused', 'draft'])
            ->having('campaign_customers_count', '>', 0)
            ->orderBy('name')->get();

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
                ])->values();

            return [$campaign->id => [
                'customer_count' => $campaign->campaign_customers_count,
                'batches'        => $batches,
            ]];
        });

        return view('admin.whatsapp-campaigns.create', compact('campaigns', 'campaignData'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'campaign_id'     => 'required|exists:campaigns,id',
            'coupon_batch_id' => 'nullable|exists:coupon_batches,id',
            'message_template'=> 'required|string|max:1000',
            'scheduled_at'    => 'nullable|date|after:now',
        ]);

        $campaign  = Campaign::findOrFail($data['campaign_id']);
        $customers = $campaign->customers()
            ->where('customers.status', 'active')
            ->get(['customers.id', 'customers.phone', 'customers.name']);

        $data['created_by_user_id'] = auth()->id();
        $data['status']             = $data['scheduled_at'] ? 'scheduled' : 'draft';
        $data['total_recipients']   = $customers->count();

        $waCampaign = WhatsAppCampaign::create($data);

        foreach ($customers->chunk(500) as $chunk) {
            $records = $chunk->map(fn($c) => [
                'whatsapp_campaign_id' => $waCampaign->id,
                'customer_id'          => $c->id,
                'phone'                => $c->phone,
                'status'               => 'pending',
                'created_at'           => now(),
            ])->toArray();
            WhatsAppRecipient::insert($records);
        }

        AuditService::log('created', WhatsAppCampaign::class, $waCampaign->id, [], $waCampaign->toArray());

        return redirect()->route('admin.whatsapp-campaigns.show', $waCampaign)
            ->with('success', "Campaña WhatsApp creada con {$customers->count()} destinatarios.");
    }

    public function show(WhatsAppCampaign $whatsAppCampaign)
    {
        $whatsAppCampaign->load(['campaign', 'couponBatch', 'createdBy']);

        $recipientStats = [
            'pending' => $whatsAppCampaign->recipients()->where('status', 'pending')->count(),
            'sent'    => $whatsAppCampaign->recipients()->where('status', 'sent')->count(),
            'failed'  => $whatsAppCampaign->recipients()->where('status', 'failed')->count(),
            'total'   => $whatsAppCampaign->total_recipients,
        ];

        $missingCount = $whatsAppCampaign->campaign_id
            ? CampaignCustomer::where('campaign_id', $whatsAppCampaign->campaign_id)
                ->join('customers', 'customers.id', '=', 'campaign_customers.customer_id')
                ->whereNotNull('customers.phone')
                ->whereNotIn('campaign_customers.customer_id',
                    WhatsAppRecipient::where('whatsapp_campaign_id', $whatsAppCampaign->id)->select('customer_id')
                )->count()
            : 0;

        $recipients = $whatsAppCampaign->recipients()
            ->with('customer')
            ->orderByRaw("FIELD(status, 'failed', 'pending', 'sent')")
            ->paginate(30);

        $availableBatches = $whatsAppCampaign->campaign_id
            ? CouponBatch::where('campaign_id', $whatsAppCampaign->campaign_id)
                ->whereIn('status', ['active', 'paused'])->orderBy('name')
                ->get(['id', 'name', 'status', 'code_type', 'general_code', 'prefix', 'discount_type', 'discount_value'])
            : collect();

        return view('admin.whatsapp-campaigns.show',
            compact('whatsAppCampaign', 'recipients', 'recipientStats', 'missingCount', 'availableBatches'));
    }

    public function syncRecipients(WhatsAppCampaign $whatsAppCampaign)
    {
        if (!$whatsAppCampaign->campaign_id) {
            return back()->with('error', 'Esta campaña no tiene una campaña vinculada.');
        }

        $missing = CampaignCustomer::where('campaign_id', $whatsAppCampaign->campaign_id)
            ->join('customers', 'customers.id', '=', 'campaign_customers.customer_id')
            ->whereNotNull('customers.phone')
            ->whereNotIn('campaign_customers.customer_id',
                WhatsAppRecipient::where('whatsapp_campaign_id', $whatsAppCampaign->id)->select('customer_id')
            )->select('customers.id', 'customers.phone')->get();

        if ($missing->isEmpty()) {
            return back()->with('info', 'Todos los clientes ya están incluidos.');
        }

        $rows = $missing->map(fn($c) => [
            'whatsapp_campaign_id' => $whatsAppCampaign->id,
            'customer_id'          => $c->id,
            'phone'                => $c->phone,
            'status'               => 'pending',
            'created_at'           => now(),
        ])->all();

        WhatsAppRecipient::insert($rows);
        $whatsAppCampaign->increment('total_recipients', count($rows));

        $shouldDispatch = in_array($whatsAppCampaign->status, ['sending', 'sent', 'failed']);
        if ($shouldDispatch) {
            ProcessWhatsAppCampaign::dispatch($whatsAppCampaign);
            $whatsAppCampaign->update(['status' => 'sending', 'finished_at' => null]);
        }

        AuditService::log('recipients_synced', WhatsAppCampaign::class, $whatsAppCampaign->id, [], [
            'added' => count($rows), 'dispatched' => $shouldDispatch,
        ]);

        $msg = count($rows) . ' clientes nuevos añadidos.';
        $msg .= $shouldDispatch ? ' Envío despachado automáticamente.' : ' Usa "Enviar ahora" cuando estés listo.';

        return back()->with('success', $msg);
    }

    public function send(WhatsAppCampaign $whatsAppCampaign)
    {
        if (!in_array($whatsAppCampaign->status, ['draft', 'scheduled', 'failed'])) {
            return back()->with('error', 'La campaña no se puede enviar en este estado.');
        }
        if ($whatsAppCampaign->total_recipients === 0) {
            return back()->with('error', 'La campaña no tiene destinatarios.');
        }

        ProcessWhatsAppCampaign::dispatch($whatsAppCampaign);
        $whatsAppCampaign->update(['status' => 'sending', 'started_at' => now()]);

        AuditService::log('sent', WhatsAppCampaign::class, $whatsAppCampaign->id, [], ['status' => 'sending']);

        return back()->with('success', 'Campaña WhatsApp despachada a la cola de envío.');
    }

    public function cancel(WhatsAppCampaign $whatsAppCampaign)
    {
        if (!in_array($whatsAppCampaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Solo se pueden cancelar campañas en estado borrador o programada.');
        }

        $whatsAppCampaign->update(['status' => 'cancelled']);
        AuditService::log('cancelled', WhatsAppCampaign::class, $whatsAppCampaign->id, [], ['status' => 'cancelled']);

        return back()->with('success', 'Campaña cancelada.');
    }

    public function retry(WhatsAppCampaign $whatsAppCampaign)
    {
        $reset = $whatsAppCampaign->recipients()->where('status', 'failed')->update([
            'status'            => 'pending',
            'error_message'     => null,
            'provider_response' => null,
        ]);

        if ($reset === 0) {
            return back()->with('error', 'No hay destinatarios fallidos para reintentar.');
        }

        ProcessWhatsAppCampaign::dispatch($whatsAppCampaign);
        $whatsAppCampaign->update(['status' => 'sending', 'started_at' => now(), 'finished_at' => null]);

        AuditService::log('retried', WhatsAppCampaign::class, $whatsAppCampaign->id, [], [
            'reset_recipients' => $reset,
        ]);

        return back()->with('success', "Reintentando envío para {$reset} destinatarios fallidos.");
    }

    public function retryRecipient(WhatsAppCampaign $whatsAppCampaign, WhatsAppRecipient $recipient)
    {
        if ($recipient->whatsapp_campaign_id !== $whatsAppCampaign->id) abort(403);
        if ($recipient->status !== 'failed') {
            return back()->with('error', 'Solo se pueden reintentar destinatarios fallidos.');
        }

        $recipient->update(['status' => 'pending', 'error_message' => null, 'provider_response' => null]);
        ProcessWhatsAppCampaign::dispatch($whatsAppCampaign);
        $whatsAppCampaign->update(['status' => 'sending', 'finished_at' => null]);

        return back()->with('success', "Reintentando envío a {$recipient->phone}.");
    }

    public function linkBatch(Request $request, WhatsAppCampaign $whatsAppCampaign)
    {
        $data = $request->validate(['coupon_batch_id' => 'nullable|exists:coupon_batches,id']);

        $old = $whatsAppCampaign->coupon_batch_id;
        $whatsAppCampaign->update(['coupon_batch_id' => $data['coupon_batch_id'] ?? null]);

        AuditService::log('batch_linked', WhatsAppCampaign::class, $whatsAppCampaign->id,
            ['coupon_batch_id' => $old],
            ['coupon_batch_id' => $whatsAppCampaign->coupon_batch_id]
        );

        return back()->with('success', $whatsAppCampaign->coupon_batch_id
            ? 'Lote de cupones vinculado.' : 'Lote de cupones desvinculado.');
    }
}
