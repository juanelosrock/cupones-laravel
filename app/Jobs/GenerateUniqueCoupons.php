<?php
namespace App\Jobs;

use App\Models\CouponBatch;
use App\Services\CouponService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateUniqueCoupons implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 3;

    public function __construct(
        private CouponBatch $batch,
        private int $quantity
    ) {}

    public function handle(CouponService $couponService): void
    {
        $generated = $couponService->generateCodes($this->batch, $this->quantity);
        Log::info("GenerateUniqueCoupons: {$generated}/{$this->quantity} codes generated for batch #{$this->batch->id}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateUniqueCoupons failed for batch #{$this->batch->id}: " . $exception->getMessage());
    }
}