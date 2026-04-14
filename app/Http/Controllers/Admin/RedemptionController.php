<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponRedemption;
use App\Services\CouponService;
use Illuminate\Http\Request;

class RedemptionController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function index(Request $request)
    {
        $query = CouponRedemption::with(['coupon.batch.campaign', 'customer', 'user'])
            ->when($request->date_from, fn($q) => $q->where('redeemed_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->where('redeemed_at', '<=', $request->date_to . ' 23:59:59'))
            ->when($request->channel,   fn($q) => $q->where('channel', $request->channel))
            ->when($request->search,    fn($q) => $q->whereHas('coupon', fn($c) => $c->where('code', 'like', "%{$request->search}%")))
            ->when($request->status === 'reversed', fn($q) => $q->whereNotNull('reversed_at'))
            ->when($request->status === 'active',   fn($q) => $q->whereNull('reversed_at'))
            ->when($request->campaign_id, fn($q) => $q->whereHas(
                'coupon.batch', fn($b) => $b->where('campaign_id', $request->campaign_id)
            ));

        $redemptions = $query->latest('redeemed_at')->paginate(30)->withQueryString();

        // KPI stats (sin aplicar filtros de paginación)
        $baseQuery = CouponRedemption::query()
            ->when($request->date_from, fn($q) => $q->where('redeemed_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->where('redeemed_at', '<=', $request->date_to . ' 23:59:59'))
            ->when($request->channel,   fn($q) => $q->where('channel', $request->channel));

        $stats = [
            'total'          => (clone $baseQuery)->count(),
            'total_discount' => (clone $baseQuery)->sum('discount_applied'),
            'total_revenue'  => (clone $baseQuery)->sum('final_amount'),
            'reversed'       => (clone $baseQuery)->whereNotNull('reversed_at')->count(),
            'today'          => CouponRedemption::whereDate('redeemed_at', today())->count(),
        ];

        return view('admin.redemptions.index', compact('redemptions', 'stats'));
    }

    public function show(CouponRedemption $redemption)
    {
        $redemption->load(['coupon.batch.campaign', 'customer.city', 'user', 'reversedBy']);
        return view('admin.redemptions.show', compact('redemption'));
    }

    public function reverse(CouponRedemption $redemption)
    {
        $result = $this->couponService->reverse($redemption->id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        return back()->with('error', $result['message']);
    }
}
