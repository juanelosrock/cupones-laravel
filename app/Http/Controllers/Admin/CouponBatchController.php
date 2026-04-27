<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateUniqueCoupons;
use App\Models\Campaign;
use App\Models\CouponBatch;
use App\Services\AuditService;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponBatchController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function index(Request $request)
    {
        $batches = CouponBatch::with(['campaign', 'createdBy'])
            ->withCount('coupons')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('admin.coupon-batches.index', compact('batches'));
    }

    public function create(Request $request)
    {
        $campaigns = Campaign::whereIn('status', ['active', 'paused', 'draft'])
            ->orderBy('name')
            ->get();
        $selectedCampaignId = $request->campaign_id;
        return view('admin.coupon-batches.create', compact('campaigns', 'selectedCampaignId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'code_type' => 'required|in:unique,general',
            'general_code' => 'required_if:code_type,general|nullable|string|max:50|unique:coupon_batches',
            'prefix' => 'nullable|string|max:20|alpha_num',
            'quantity' => 'required_if:code_type,unique|nullable|integer|min:1|max:100000',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01|max:100',
            'min_purchase_amount'  => 'required|numeric|min:0',
            'max_purchase_amount'  => 'nullable|numeric|min:0',
            'max_discount_amount'  => 'nullable|numeric|min:0',
            'max_uses_total'       => 'nullable|integer|min:1',
            'max_uses_per_user'    => 'nullable|integer|min:1',
            'max_uses_per_day'     => 'nullable|integer|min:1',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
            'is_combinable'        => 'boolean',
            'applicable_to'        => 'required|in:all,product,category,pos,zone,city',
        ]);

        $data['created_by_user_id'] = auth()->id();
        $data['is_combinable'] = $request->boolean('is_combinable');

        $batch = CouponBatch::create($data);

        if ($batch->code_type === 'unique') {
            GenerateUniqueCoupons::dispatch($batch, $data['quantity'] ?? 1);
        }

        AuditService::log('created', CouponBatch::class, $batch->id, [], $batch->toArray());

        return redirect()->route('admin.coupon-batches.show', $batch)
            ->with('success', 'Lote de cupones creado. Los códigos se están generando.');
    }

    public function show(CouponBatch $couponBatch)
    {
        $couponBatch->load(['campaign', 'createdBy', 'restrictions']);
        $coupons = $couponBatch->coupons()->latest()->paginate(50);
        $redemptionStats = [
            'total' => $couponBatch->coupons()->sum('times_used'),
            'today' => \App\Models\CouponRedemption::whereHas('coupon', fn($q) => $q->where('batch_id', $couponBatch->id))
                ->whereDate('redeemed_at', today())->count(),
            'total_discount' => \App\Models\CouponRedemption::whereHas('coupon', fn($q) => $q->where('batch_id', $couponBatch->id))
                ->sum('discount_applied'),
        ];

        return view('admin.coupon-batches.show', compact('couponBatch', 'coupons', 'redemptionStats'));
    }

    public function edit(CouponBatch $couponBatch)
    {
        $campaigns = Campaign::whereIn('status', ['active', 'paused', 'draft'])
            ->orderBy('name')
            ->get();
        return view('admin.coupon-batches.edit', compact('couponBatch', 'campaigns'));
    }

    public function update(Request $request, CouponBatch $couponBatch)
    {
        $data = $request->validate([
            'campaign_id'        => 'nullable|exists:campaigns,id',
            'name'               => 'required|string|max:150',
            'description'        => 'nullable|string',
            'discount_type'      => 'required|in:percentage,fixed',
            'discount_value'     => 'required|numeric|min:0.01',
            'min_purchase_amount' => 'required|numeric|min:0',
            'max_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses_total'      => 'nullable|integer|min:1',
            'max_uses_per_user'   => 'nullable|integer|min:1',
            'max_uses_per_day'    => 'nullable|integer|min:1',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'is_combinable'      => 'boolean',
            'applicable_to'      => 'required|in:all,product,category,pos,zone,city',
            'status'             => 'required|in:draft,active,paused,expired,cancelled',
        ]);

        $data['is_combinable'] = $request->boolean('is_combinable');

        $old = $couponBatch->toArray();
        $couponBatch->update($data);
        AuditService::log('updated', CouponBatch::class, $couponBatch->id, $old, $couponBatch->fresh()->toArray());

        return redirect()->route('admin.coupon-batches.show', $couponBatch)
            ->with('success', 'Lote actualizado correctamente.');
    }

    public function activate(CouponBatch $couponBatch)
    {
        $couponBatch->update(['status' => 'active']);
        AuditService::log('activated', CouponBatch::class, $couponBatch->id, [], ['status' => 'active']);
        return back()->with('success', 'Lote activado.');
    }

    public function pause(CouponBatch $couponBatch)
    {
        $couponBatch->update(['status' => 'paused']);
        AuditService::log('paused', CouponBatch::class, $couponBatch->id, [], ['status' => 'paused']);
        return back()->with('success', 'Lote pausado.');
    }
}