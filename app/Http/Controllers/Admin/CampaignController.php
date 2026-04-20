<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CampaignCustomersImport;
use App\Models\Campaign;
use App\Models\CampaignCustomer;
use App\Models\CampaignLocation;
use App\Models\City;
use App\Models\CouponBatch;
use App\Models\CouponRedemption;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Department;
use App\Models\PointOfSale;
use App\Models\Zone;
use App\Models\SmsCampaign;
use App\Models\SmsRecipient;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::withCount('couponBatches')
            ->withCount(['couponBatches as active_batches_count' => fn($q) => $q->where('status', 'active')])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type));

        $campaigns = $query->latest()->paginate(20)->withQueryString();

        // Summary cards
        $stats = [
            'total'     => Campaign::count(),
            'active'    => Campaign::where('status', 'active')->count(),
            'draft'     => Campaign::where('status', 'draft')->count(),
            'paused'    => Campaign::where('status', 'paused')->count(),
            'finished'  => Campaign::whereIn('status', ['finished', 'cancelled'])->count(),
        ];

        return view('admin.campaigns.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        $zones        = Zone::with('city')->where('is_active', true)->orderBy('city_id')->orderBy('name')->get();
        $pointsOfSale = PointOfSale::with(['city', 'zone'])->where('status', 'active')->orderBy('name')->get();
        return view('admin.campaigns.create', compact('zones', 'pointsOfSale'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'type'        => 'required|in:general,sms,product,activation,autorizacion',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'budget'      => 'nullable|numeric|min:0',
            'status'      => 'required|in:draft,active,paused',
        ]);
        $data['created_by_user_id'] = auth()->id();
        $campaign = Campaign::create($data);

        $this->syncLocations($campaign, $request);

        AuditService::log('created', Campaign::class, $campaign->id, [], $campaign->toArray());
        return redirect()->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaña creada exitosamente.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['couponBatches.coupons', 'createdBy', 'zones.city', 'pointsOfSale.city', 'pointsOfSale.zone']);

        // Aggregate stats
        $batchIds = $campaign->couponBatches->pluck('id');

        $totalCoupons = $campaign->couponBatches->sum(fn($b) => $b->coupons->count());

        $totalRedemptions = CouponRedemption::whereHas(
            'coupon', fn($q) => $q->whereIn('batch_id', $batchIds)
        )->count();

        $totalDiscount = CouponRedemption::whereHas(
            'coupon', fn($q) => $q->whereIn('batch_id', $batchIds)
        )->sum('discount_applied');

        $totalRevenue = CouponRedemption::whereHas(
            'coupon', fn($q) => $q->whereIn('batch_id', $batchIds)
        )->sum('final_amount');

        $redemptionRate = $totalCoupons > 0
            ? round(($totalRedemptions / $totalCoupons) * 100, 1)
            : 0;

        $budgetUsed = $campaign->budget && $campaign->budget > 0
            ? round(($totalDiscount / $campaign->budget) * 100, 1)
            : null;

        // Recent redemptions (last 10)
        $recentRedemptions = CouponRedemption::with(['coupon.batch', 'customer'])
            ->whereHas('coupon', fn($q) => $q->whereIn('batch_id', $batchIds))
            ->orderByDesc('redeemed_at')
            ->limit(10)
            ->get();

        // Activity log
        $activityLog = AuditLog::where('auditable_type', Campaign::class)
            ->where('auditable_id', $campaign->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Batch stats per batch
        $batchStats = $campaign->couponBatches->map(function ($batch) {
            $redeemed = CouponRedemption::whereHas(
                'coupon', fn($q) => $q->where('batch_id', $batch->id)
            )->count();
            $discount = CouponRedemption::whereHas(
                'coupon', fn($q) => $q->where('batch_id', $batch->id)
            )->sum('discount_applied');
            $total = $batch->coupons->count();
            return [
                'batch'      => $batch,
                'total'      => $total,
                'redeemed'   => $redeemed,
                'discount'   => $discount,
                'rate'       => $total > 0 ? round(($redeemed / $total) * 100, 1) : 0,
            ];
        });

        // Analytics geográfico: redenciones por ciudad de los clientes
        $geoStats = CouponRedemption::selectRaw('customers.city_id, COUNT(*) as total, SUM(discount_applied) as discount')
            ->join('customers', 'coupon_redemptions.customer_id', '=', 'customers.id')
            ->whereHas('coupon', fn($q) => $q->whereIn('batch_id', $batchIds))
            ->whereNotNull('customers.city_id')
            ->groupBy('customers.city_id')
            ->with('customer') // not needed here
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Load city names
        $cityIds   = $geoStats->pluck('city_id');
        $cities    = \App\Models\City::whereIn('id', $cityIds)->pluck('name', 'id');

        return view('admin.campaigns.show', compact(
            'campaign',
            'totalCoupons',
            'totalRedemptions',
            'totalDiscount',
            'totalRevenue',
            'redemptionRate',
            'budgetUsed',
            'recentRedemptions',
            'activityLog',
            'batchStats',
            'geoStats',
            'cities'
        ));
    }

    public function edit(Campaign $campaign)
    {
        $zones           = Zone::with('city')->where('is_active', true)->orderBy('city_id')->orderBy('name')->get();
        $pointsOfSale    = PointOfSale::with(['city', 'zone'])->where('status', 'active')->orderBy('name')->get();
        $selectedZones   = $campaign->zones()->pluck('zones.id')->toArray();
        $selectedPOS     = $campaign->pointsOfSale()->pluck('points_of_sale.id')->toArray();
        return view('admin.campaigns.edit', compact('campaign', 'zones', 'pointsOfSale', 'selectedZones', 'selectedPOS'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'type'        => 'required|in:general,sms,product,activation,autorizacion',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'budget'      => 'nullable|numeric|min:0',
            'status'      => 'required|in:draft,active,paused,finished,cancelled',
        ]);
        $old = $campaign->toArray();
        $campaign->update($data);

        $this->syncLocations($campaign, $request);

        AuditService::log('updated', Campaign::class, $campaign->id, $old, $campaign->fresh()->toArray());
        return redirect()->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaña actualizada.');
    }

    public function activate(Campaign $campaign)
    {
        $old = $campaign->toArray();
        $campaign->update(['status' => 'active']);
        AuditService::log('activated', Campaign::class, $campaign->id, $old, ['status' => 'active']);
        return back()->with('success', 'Campaña activada.');
    }

    public function pause(Campaign $campaign)
    {
        $old = $campaign->toArray();
        $campaign->update(['status' => 'paused']);
        AuditService::log('paused', Campaign::class, $campaign->id, $old, ['status' => 'paused']);
        return back()->with('success', 'Campaña pausada.');
    }

    public function duplicate(Campaign $campaign)
    {
        $new = $campaign->replicate();
        $new->name = $campaign->name . ' (copia)';
        $new->status = 'draft';
        $new->created_by_user_id = auth()->id();
        $new->save();
        AuditService::log('duplicated', Campaign::class, $new->id, [], $new->toArray());
        return redirect()->route('admin.campaigns.edit', $new)
            ->with('success', 'Campaña duplicada. Revisa y ajusta los datos antes de activarla.');
    }

    public function destroy(Campaign $campaign)
    {
        $old = $campaign->toArray();
        $campaign->delete();
        AuditService::log('deleted', Campaign::class, $campaign->id, $old, []);
        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaña eliminada.');
    }

    // ── Asignación por segmentación ───────────────────────────────────────────

    public function assignForm(Campaign $campaign)
    {
        $departments = Department::orderBy('name')->get();
        $cities      = City::with('department')->orderBy('name')->get();
        return view('admin.campaigns.assign', compact('campaign', 'departments', 'cities'));
    }

    public function assign(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'department_ids'   => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
            'city_ids'         => 'nullable|array',
            'city_ids.*'       => 'integer|exists:cities,id',
        ]);

        $query = $this->buildAssignQuery($campaign, $data['department_ids'] ?? [], $data['city_ids'] ?? []);

        // Excluir clientes ya asignados
        $already = CampaignCustomer::where('campaign_id', $campaign->id)->pluck('customer_id');
        $query->whereNotIn('customers.id', $already);

        $customers = $query->select('customers.id', 'customers.phone')->get();

        if ($customers->isEmpty()) {
            return back()->with('error', 'No hay clientes que cumplan los filtros seleccionados.');
        }

        $batch = (string) Str::uuid();
        $rows  = $customers->map(fn($c) => [
            'campaign_id'  => $campaign->id,
            'customer_id'  => $c->id,
            'source'       => 'filter',
            'import_batch' => $batch,
            'created_at'   => now(),
        ])->all();

        CampaignCustomer::insert($rows);

        $smsAdded = $this->syncSmsRecipients($campaign, $customers->pluck('phone', 'id'));

        AuditService::log('customers_assigned', Campaign::class, $campaign->id, [], [
            'assigned'   => count($rows),
            'sms_synced' => $smsAdded,
            'filters'    => $data,
            'batch'      => $batch,
        ]);

        $msg = count($rows) . ' clientes asignados a la campaña.';
        if ($smsAdded > 0) {
            $msg .= " {$smsAdded} añadidos también a la campaña SMS asociada.";
        }

        return redirect()->route('admin.campaigns.customers', $campaign)
            ->with('success', $msg);
    }

    public function assignPreview(Request $request, Campaign $campaign): JsonResponse
    {
        $deptIds = array_filter((array) $request->input('department_ids', []));
        $cityIds = array_filter((array) $request->input('city_ids', []));

        $query = $this->buildAssignQuery($campaign, $deptIds, $cityIds);

        $already = CampaignCustomer::where('campaign_id', $campaign->id)->pluck('customer_id');
        $query->whereNotIn('customers.id', $already);

        return response()->json([
            'count'    => $query->count(),
            'existing' => (int) $already->count(),
        ]);
    }

    /** Construye la query base de clientes para asignación según tipo de campaña y filtros geográficos. */
    private function buildAssignQuery(Campaign $campaign, array $deptIds, array $cityIds)
    {
        $query = Customer::query()->where('customers.status', 'active');

        // Regla por tipo de campaña
        if ($campaign->type === 'autorizacion') {
            // Solo clientes SIN autorización
            $query->where(function ($q) {
                $q->where('data_treatment_accepted', false)
                  ->orWhereNull('data_treatment_accepted');
            });
        } else {
            // Cualquier otro tipo: solo clientes CON autorización
            $query->where('data_treatment_accepted', true);
        }

        // Filtro geográfico: ciudades tienen prioridad sobre departamentos
        if (!empty($cityIds)) {
            $query->whereIn('city_id', $cityIds);
        } elseif (!empty($deptIds)) {
            $query->whereHas('city', fn($q) => $q->whereIn('department_id', $deptIds));
        }

        return $query;
    }

    public function importTemplate()
    {
        $csv = "telefono,nombre,apellido,documento,email,departamento,ciudad\n";
        $csv .= "3001234567,María,García,1020304050,maria@email.com,Cundinamarca,Bogotá\n";
        $csv .= "3109876543,Carlos,Rodríguez,987654321,carlos@email.com,Antioquia,Medellín\n";
        $csv .= "3201112233,Laura,Pérez,,laura@email.com,Valle del Cauca,Cali\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_clientes.csv"',
        ]);
    }

    public function importForm(Campaign $campaign)
    {
        return view('admin.campaigns.import', compact('campaign'));
    }

    public function import(Request $request, Campaign $campaign)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ], [
            'file.required' => 'Debes seleccionar un archivo.',
            'file.mimes'    => 'Solo se permiten archivos CSV, TXT, XLS o XLSX.',
            'file.max'      => 'El archivo no puede superar 5 MB.',
        ]);

        $importer = new CampaignCustomersImport($campaign);

        Excel::import($importer, $request->file('file'));

        $msg = "Importación completa: {$importer->imported} clientes nuevos, {$importer->updated} actualizados, {$importer->skipped} omitidos.";
        if ($importer->skippedAuthorized > 0) {
            $msg .= " {$importer->skippedAuthorized} excluidos por tener autorización de datos ya registrada.";
        }

        // Sincronizar nuevos clientes con campañas SMS asociadas
        $smsAdded = 0;
        if ($importer->imported > 0) {
            $newCustomers = CampaignCustomer::where('campaign_id', $campaign->id)
                ->where('import_batch', $importer->getImportBatch())
                ->join('customers', 'customers.id', '=', 'campaign_customers.customer_id')
                ->select('customers.id', 'customers.phone')
                ->get();

            $smsAdded = $this->syncSmsRecipients($campaign, $newCustomers->pluck('phone', 'id'));
        }

        if ($smsAdded > 0) {
            $msg .= " {$smsAdded} añadidos también a la campaña SMS asociada.";
        }

        AuditService::log('customers_imported', Campaign::class, $campaign->id, [], [
            'imported'   => $importer->imported,
            'updated'    => $importer->updated,
            'skipped'    => $importer->skipped,
            'sms_synced' => $smsAdded,
            'batch'      => $importer->getImportBatch(),
        ]);

        return redirect()
            ->route('admin.campaigns.assign', $campaign)
            ->with('success', $msg)
            ->with('import_errors', $importer->errors);
    }

    public function customers(Request $request, Campaign $campaign)
    {
        $customers = $campaign->customers()
            ->with('city')
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->where('customers.name', 'like', "%{$request->search}%")
                  ->orWhere('customers.phone', 'like', "%{$request->search}%")
                  ->orWhere('customers.document_number', 'like', "%{$request->search}%");
            }))
            ->orderByPivot('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('admin.campaigns.customers', compact('campaign', 'customers'));
    }

    public function removeCustomer(Campaign $campaign, $customerId)
    {
        $campaign->customers()->detach($customerId);
        return back()->with('success', 'Cliente eliminado de la campaña.');
    }

    /**
     * Crea SmsRecipient para cada cliente nuevo en las campañas SMS activas vinculadas.
     * Recibe un mapa [customer_id => phone].
     */
    private function syncSmsRecipients(Campaign $campaign, \Illuminate\Support\Collection $phoneById): int
    {
        if ($phoneById->isEmpty()) return 0;

        $smsCampaigns = SmsCampaign::where('campaign_id', $campaign->id)
            ->whereNotIn('status', ['sent', 'finished', 'cancelled'])
            ->get();

        if ($smsCampaigns->isEmpty()) return 0;

        $customerIds = $phoneById->keys();
        $totalAdded  = 0;

        foreach ($smsCampaigns as $smsCampaign) {
            $existing = SmsRecipient::where('sms_campaign_id', $smsCampaign->id)
                ->whereIn('customer_id', $customerIds)
                ->pluck('customer_id');

            $newIds = $customerIds->diff($existing);
            if ($newIds->isEmpty()) continue;

            $rows = [];
            foreach ($newIds as $customerId) {
                $phone = $phoneById[$customerId] ?? null;
                if (!$phone) continue;

                $rows[] = [
                    'sms_campaign_id' => $smsCampaign->id,
                    'customer_id'     => $customerId,
                    'phone'           => $phone,
                    'consent_token'   => $smsCampaign->send_consent_link ? (string) Str::uuid() : null,
                    'status'          => 'pending',
                    'created_at'      => now(),
                ];
            }

            if (!empty($rows)) {
                SmsRecipient::insert($rows);
                $smsCampaign->increment('total_recipients', count($rows));
                $totalAdded += count($rows);
            }
        }

        return $totalAdded;
    }

    /** Sincroniza zonas y PDVs seleccionados en el formulario. */
    private function syncLocations(Campaign $campaign, Request $request): void
    {
        // Borrar locations actuales y reinsertar
        $campaign->locations()->delete();

        $inserts = [];

        foreach (array_filter((array) $request->input('zone_ids', [])) as $zoneId) {
            $inserts[] = [
                'campaign_id'    => $campaign->id,
                'locatable_type' => Zone::class,
                'locatable_id'   => (int) $zoneId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        foreach (array_filter((array) $request->input('pos_ids', [])) as $posId) {
            $inserts[] = [
                'campaign_id'    => $campaign->id,
                'locatable_type' => PointOfSale::class,
                'locatable_id'   => (int) $posId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        if (!empty($inserts)) {
            CampaignLocation::insert($inserts);
        }
    }
}
