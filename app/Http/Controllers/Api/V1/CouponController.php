<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    /**
     * Valida un cupón sin redimirlo.
     * POST /api/v1/coupons/validate
     *
     * Siempre retorna HTTP 200. Verificar el campo "valid" para saber el resultado.
     */
    public function validate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50',
            'amount'          => 'required|numeric|min:0',
            'phone'           => 'nullable|string|max:20',
            'document_number' => 'nullable|string|max:30',
            'point_of_sale_id'=> 'nullable|integer|exists:points_of_sale,id',
        ]);

        $customerId = $this->resolveCustomerId($data['phone'] ?? null, $data['document_number'] ?? null);

        $result = $this->couponService->validate(
            strtoupper(trim($data['code'])),
            (float) $data['amount'],
            $customerId
        );

        return response()->json(array_merge($result, [
            'meta' => [
                'request_id'   => (string) Str::uuid(),
                'processed_at' => now()->toIso8601String(),
            ],
        ]));
    }

    /**
     * Redime un cupón (operación de escritura — consume un uso).
     * POST /api/v1/coupons/redeem
     */
    public function redeem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50',
            'amount'          => 'required|numeric|min:0',
            'phone'           => 'nullable|string|max:20',
            'document_number' => 'nullable|string|max:30',
            'point_of_sale_id'=> 'nullable|integer|exists:points_of_sale,id',
            'channel'         => 'nullable|in:api,web,manual,sms,pos,app',
            'order_id'        => 'nullable|string|max:100',
        ]);

        $customerId = $this->resolveCustomerId($data['phone'] ?? null, $data['document_number'] ?? null);

        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer && $customer->status === 'blocked') {
                return response()->json([
                    'error'   => 'customer_blocked',
                    'message' => 'El cliente está bloqueado y no puede redimir cupones.',
                ], 403);
            }
        }

        $result = $this->couponService->redeem(
            code:           strtoupper(trim($data['code'])),
            amount:         (float) $data['amount'],
            customerId:     $customerId,
            orderReference: $data['order_id'] ?? null,
            channel:        $data['channel'] ?? 'api',
            ip:             $request->ip(),
            userAgent:      $request->userAgent()
        );

        $status = ($result['valid'] ?? false) ? 200 : 422;

        return response()->json(array_merge($result, [
            'meta' => [
                'request_id'   => (string) Str::uuid(),
                'processed_at' => now()->toIso8601String(),
            ],
        ]), $status);
    }

    /**
     * Info pública de un cupón.
     * GET /api/v1/coupons/{code}
     */
    public function show(string $code): JsonResponse
    {
        $code = strtoupper(trim($code));

        $coupon = \App\Models\Coupon::with('batch')->where('code', $code)->first();

        if (!$coupon) {
            $batch = \App\Models\CouponBatch::where('general_code', $code)->first();
            if (!$batch) {
                return response()->json([
                    'error'   => 'not_found',
                    'message' => 'Cupón no encontrado.',
                ], 404);
            }
            return response()->json([
                'code'                => $code,
                'type'                => 'general',
                'discount_type'       => $batch->discount_type,
                'discount_value'      => (float) $batch->discount_value,
                'max_discount_amount' => $batch->max_discount_amount ? (float) $batch->max_discount_amount : null,
                'starts_at'           => $batch->start_date->toDateString(),
                'expires_at'          => $batch->end_date->toDateString(),
                'min_purchase'        => (float) $batch->min_purchase_amount,
                'max_purchase'        => $batch->max_purchase_amount ? (float) $batch->max_purchase_amount : null,
                'status'              => $batch->status,
                'uses_remaining'      => $batch->max_uses_total
                    ? $batch->max_uses_total - \App\Models\CouponRedemption::whereHas('coupon', fn($q) => $q->where('batch_id', $batch->id))->count()
                    : null,
                'applicable_to'       => $batch->applicable_to,
                'meta'                => ['request_id' => (string) Str::uuid(), 'processed_at' => now()->toIso8601String()],
            ]);
        }

        $batch = $coupon->batch;
        return response()->json([
            'code'                => $coupon->code,
            'type'                => 'unique',
            'status'              => $coupon->status,
            'discount_type'       => $batch->discount_type,
            'discount_value'      => (float) $batch->discount_value,
            'max_discount_amount' => $batch->max_discount_amount ? (float) $batch->max_discount_amount : null,
            'starts_at'           => $batch->start_date->toDateString(),
            'expires_at'          => $batch->end_date->toDateString(),
            'min_purchase'        => (float) $batch->min_purchase_amount,
            'max_purchase'        => $batch->max_purchase_amount ? (float) $batch->max_purchase_amount : null,
            'is_usable'           => $coupon->isUsable(),
            'applicable_to'       => $batch->applicable_to,
            'meta'                => ['request_id' => (string) Str::uuid(), 'processed_at' => now()->toIso8601String()],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveCustomerId(?string $phone, ?string $documentNumber): ?int
    {
        if (!$phone && !$documentNumber) {
            return null;
        }

        $customer = Customer::when($phone, fn($q) => $q->where('phone', $phone))
            ->when($documentNumber, fn($q, $v) => $q->orWhere('document_number', $v))
            ->first();

        return $customer?->id;
    }
}
