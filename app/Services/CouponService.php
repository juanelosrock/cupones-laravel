<?php
namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponBatch;
use App\Models\CouponRedemption;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Valida un cupón sin redimirlo.
     * Retorna array con 'valid', 'message', y datos del descuento.
     */
    public function validate(string $code, float $amount, ?int $customerId = null, ?array $context = []): array
    {
        $coupon = $this->findCoupon($code);

        if (!$coupon) {
            return $this->invalid('Cupón no encontrado.');
        }

        $batch = $coupon->batch;

        if (!$batch || $batch->status !== 'active') {
            return $this->invalid('Cupón no está activo.');
        }

        $today = now()->toDateString();
        if ($today < $batch->start_date->toDateString()) {
            return $this->invalid("El cupón aún no es válido. Inicia el {$batch->start_date->format('d/m/Y')}.");
        }

        if ($today > $batch->end_date->toDateString()) {
            return $this->invalid('El cupón ha vencido.');
        }

        if ($coupon->status !== 'active') {
            return $this->invalid('El cupón no está disponible (estado: ' . $coupon->status . ').');
        }

        if ($batch->code_type === 'unique' && $coupon->times_used >= 1) {
            return $this->invalid('Este cupón ya fue utilizado.');
        }

        if ($amount < $batch->min_purchase_amount) {
            $min = number_format($batch->min_purchase_amount, 0, ',', '.');
            return $this->invalid("Compra mínima requerida: \${$min}.");
        }

        if ($batch->max_purchase_amount && $amount > $batch->max_purchase_amount) {
            $max = number_format($batch->max_purchase_amount, 0, ',', '.');
            return $this->invalid("Compra máxima permitida: \${$max}.");
        }

        // Verificar max_uses_total
        if ($batch->max_uses_total) {
            $totalUsed = CouponRedemption::whereHas('coupon', fn($q) => $q->where('batch_id', $batch->id))->count();
            if ($totalUsed >= $batch->max_uses_total) {
                return $this->invalid('Se ha alcanzado el límite total de usos para este cupón.');
            }
        }

        // Verificar max_uses_per_user
        if ($customerId && $batch->max_uses_per_user) {
            $userUses = CouponRedemption::where('customer_id', $customerId)
                ->whereHas('coupon', fn($q) => $q->where('batch_id', $batch->id))
                ->count();
            if ($userUses >= $batch->max_uses_per_user) {
                return $this->invalid('Ya alcanzaste el límite de usos de este cupón.');
            }
        }

        // Verificar max_uses_per_day
        if ($batch->max_uses_per_day) {
            $todayUses = CouponRedemption::whereHas('coupon', fn($q) => $q->where('batch_id', $batch->id))
                ->whereDate('redeemed_at', today())
                ->count();
            if ($todayUses >= $batch->max_uses_per_day) {
                return $this->invalid('Se alcanzó el límite diario de usos para este cupón.');
            }
        }

        [$discountAmount, $discountCapped] = $this->calculateDiscount($batch, $amount);
        $finalAmount = max(0, $amount - $discountAmount);

        // Effective discount_value: recalculate percentage from capped amount so the caller
        // always knows the real rate that was applied (e.g. 40% instead of 50% when capped).
        $effectiveValue = $batch->discount_type === 'percentage' && $amount > 0
            ? round(($discountAmount / $amount) * 100, 4)
            : (float) $batch->discount_value;

        $usesRemaining = null;
        if ($batch->max_uses_total) {
            $used = CouponRedemption::whereHas('coupon', fn($q) => $q->where('batch_id', $batch->id))->count();
            $usesRemaining = $batch->max_uses_total - $used;
        }

        $message = 'Cupón válido.';
        if ($discountCapped) {
            $cap = number_format((float) $batch->max_discount_amount, 0, ',', '.');
            $message = "Cupón válido. Descuento máximo aplicado: \${$cap}.";
        }

        return [
            'valid'           => true,
            'code'            => $coupon->code,
            'discount_type'   => $batch->discount_type,
            'discount_value'  => (float) $batch->discount_value,
            'effective_discount_value' => $effectiveValue,
            'discount_capped' => $discountCapped,
            'max_discount_amount' => $batch->max_discount_amount ? (float) $batch->max_discount_amount : null,
            'discount_amount' => round($discountAmount, 2),
            'original_amount' => round($amount, 2),
            'final_amount'    => round($finalAmount, 2),
            'message'         => $message,
            'coupon' => [
                'id'            => $coupon->id,
                'batch_id'      => $batch->id,
                'batch_name'    => $batch->name,
                'starts_at'     => $batch->start_date->toDateString(),
                'expires_at'    => $batch->end_date->toDateString(),
                'min_purchase'  => (float) $batch->min_purchase_amount,
                'max_purchase'  => $batch->max_purchase_amount ? (float) $batch->max_purchase_amount : null,
                'uses_remaining'=> $usesRemaining,
                'applicable_to' => $batch->applicable_to,
                'is_combinable' => $batch->is_combinable,
            ],
        ];
    }

    /**
     * Redime un cupón. Si es válido, registra la redención y actualiza el contador.
     */
    public function redeem(
        string $code,
        float $amount,
        ?int $customerId = null,
        ?int $userId = null,
        ?string $orderReference = null,
        string $channel = 'api',
        ?string $ip = null,
        ?string $userAgent = null,
        array $metadata = []
    ): array {
        return DB::transaction(function () use ($code, $amount, $customerId, $userId, $orderReference, $channel, $ip, $userAgent, $metadata) {
            // Usar FOR UPDATE para prevenir race conditions
            $coupon = $this->findCoupon($code, forUpdate: true);

            $validation = $this->validate($code, $amount, $customerId);

            if (!$validation['valid']) {
                return $validation;
            }

            $batch = $coupon->batch;
            [$discountAmount] = $this->calculateDiscount($batch, $amount);
            $finalAmount = max(0, $amount - $discountAmount);

            // Registrar redención
            $redemption = CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'customer_id' => $customerId,
                'user_id' => $userId,
                'order_reference' => $orderReference,
                'original_amount' => $amount,
                'discount_applied' => round($discountAmount, 2),
                'final_amount' => round($finalAmount, 2),
                'channel' => $channel,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'metadata' => $metadata ?: null,
                'redeemed_at' => now(),
            ]);

            // Actualizar contador
            $coupon->increment('times_used');

            // Si es único y ya se usó, marcarlo como usado
            if ($batch->code_type === 'unique') {
                $coupon->update(['status' => 'used']);
            }

            return array_merge($validation, [
                'redeemed' => true,
                'redemption_id' => $redemption->id,
                'message' => 'Cupón redimido exitosamente.',
            ]);
        });
    }

    /**
     * Reversa una redención (anula el descuento aplicado).
     */
    public function reverse(int $redemptionId, int $reversedByUserId): array
    {
        $redemption = CouponRedemption::findOrFail($redemptionId);

        if ($redemption->reversed_at) {
            return ['success' => false, 'message' => 'Esta redención ya fue revertida.'];
        }

        DB::transaction(function () use ($redemption, $reversedByUserId) {
            $redemption->update([
                'reversed_at' => now(),
                'reversed_by_user_id' => $reversedByUserId,
            ]);

            $coupon = $redemption->coupon;
            $coupon->decrement('times_used');

            if ($coupon->status === 'used') {
                $coupon->update(['status' => 'active']);
            }
        });

        return ['success' => true, 'message' => 'Redención revertida correctamente.'];
    }

    /**
     * Genera códigos únicos para un lote.
     */
    public function generateCodes(CouponBatch $batch, int $quantity): int
    {
        $generated = 0;
        $attempts = 0;
        $maxAttempts = $quantity * 3;

        while ($generated < $quantity && $attempts < $maxAttempts) {
            $code = $this->generateCode($batch->prefix);
            $exists = Coupon::where('code', $code)->exists();

            if (!$exists) {
                Coupon::create([
                    'batch_id' => $batch->id,
                    'code' => $code,
                    'status' => 'active',
                    'times_used' => 0,
                    'created_at' => now(),
                ]);
                $generated++;
            }
            $attempts++;
        }

        return $generated;
    }

    private function findCoupon(string $code, bool $forUpdate = false): ?Coupon
    {
        // Buscar en cupones únicos primero
        $query = Coupon::with('batch')->where('code', strtoupper(trim($code)));
        if ($forUpdate) $query->lockForUpdate();
        $coupon = $query->first();

        if ($coupon) return $coupon;

        // Buscar en cupones generales (código del batch)
        $batch = CouponBatch::where('general_code', strtoupper(trim($code)))->first();
        if (!$batch) return null;

        // Crear un "pseudo-coupon" para el general
        return Coupon::firstOrCreate(
            ['code' => strtoupper(trim($code)), 'batch_id' => $batch->id],
            ['status' => 'active', 'times_used' => 0, 'created_at' => now()]
        );
    }

    /** @return array{float, bool} [discountAmount, wasCapped] */
    private function calculateDiscount(CouponBatch $batch, float $amount): array
    {
        $discount = $batch->discount_type === 'percentage'
            ? $amount * ((float) $batch->discount_value / 100)
            : min((float) $batch->discount_value, $amount);

        $cap = $batch->max_discount_amount ? (float) $batch->max_discount_amount : null;

        if ($cap !== null && $discount > $cap) {
            return [$cap, true];
        }

        return [$discount, false];
    }

    private function generateCode(?string $prefix = null): string
    {
        $prefix = strtoupper($prefix ?? '');
        $random = strtoupper(Str::random(8));
        return $prefix ? "{$prefix}{$random}" : $random;
    }

    private function invalid(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'discount_amount' => 0,
            'final_amount' => null,
        ];
    }
}