<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CouponBatch;
use App\Models\CouponRedemption;
use App\Models\Customer;
use App\Models\Coupon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_batches' => CouponBatch::where('status', 'active')->count(),
            'total_coupons' => Coupon::count(),
            'redeemed_today' => CouponRedemption::whereDate('redeemed_at', today())->count(),
            'redeemed_month' => CouponRedemption::whereMonth('redeemed_at', now()->month)->count(),
            'total_customers' => Customer::count(),
            'new_customers_week' => Customer::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'discount_given_month' => CouponRedemption::whereMonth('redeemed_at', now()->month)->sum('discount_applied'),
        ];

        $recentRedemptions = CouponRedemption::with(['coupon.batch', 'customer'])
            ->latest('redeemed_at')
            ->limit(10)
            ->get();

        $topBatches = CouponBatch::withCount(['coupons as total_redeemed' => fn($q) => $q->where('status', 'used')])
            ->orderByDesc('total_redeemed')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRedemptions', 'topBatches'));
    }
}