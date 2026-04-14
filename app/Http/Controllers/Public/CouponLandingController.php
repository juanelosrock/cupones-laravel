<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponBatch;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponLandingController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function show(string $code)
    {
        $code = strtoupper(trim($code));

        // Buscar cupón único
        $coupon = Coupon::with('batch')->where('code', $code)->first();
        $batch = $coupon?->batch ?? CouponBatch::where('general_code', $code)->first();

        if (!$batch) {
            return view('public.coupon-not-found', compact('code'));
        }

        return view('public.coupon-landing', compact('code', 'coupon', 'batch'));
    }

    public function check(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
        ]);

        $result = $this->couponService->validate(strtoupper($data['code']), (float)$data['amount']);
        return response()->json($result);
    }
}