<?php
namespace App\Jobs;

use App\Models\Coupon;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppRecipient;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 1;

    public function __construct(private WhatsAppCampaign $campaign) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $this->campaign->update(['status' => 'sending', 'started_at' => now()]);

        $recipients = $this->campaign->recipients()->where('status', 'pending')->get();
        $batch      = $this->campaign->couponBatch;

        foreach ($recipients as $recipient) {
            try {
                $vars = [
                    'name'  => $recipient->customer?->name ?? 'Cliente',
                    'phone' => $recipient->phone,
                ];

                if ($batch) {
                    if ($batch->code_type === 'general') {
                        $vars['code']     = $batch->general_code;
                        $vars['discount'] = $this->formatDiscount($batch);
                    } elseif ($batch->code_type === 'unique') {
                        $code = $this->reserveUniqueCode($batch->id, $recipient);
                        if ($code) {
                            $vars['code']     = $code;
                            $vars['discount'] = $this->formatDiscount($batch);
                        }
                    }
                }

                $message = $whatsApp->renderTemplate($this->campaign->message_template, $vars);
                $result  = $whatsApp->send($recipient->phone, $message);

                $recipient->update([
                    'status'            => $result['success'] ? 'sent' : 'failed',
                    'message_sent'      => $message,
                    'sent_at'           => $result['success'] ? now() : null,
                    'error_message'     => $result['success'] ? null : $result['message'],
                    'provider_response' => json_encode($result),
                ]);

                if ($result['success']) {
                    $this->campaign->increment('sent_count');
                } else {
                    $this->campaign->increment('failed_count');
                }

                usleep(100_000); // 100 ms entre envíos

            } catch (\Throwable $e) {
                Log::error("WhatsApp campaign #{$this->campaign->id} error for recipient #{$recipient->id}: " . $e->getMessage());
                $recipient->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $this->campaign->increment('failed_count');
            }
        }

        $this->campaign->update(['status' => 'sent', 'finished_at' => now()]);
    }

    private function reserveUniqueCode(int $batchId, WhatsAppRecipient $recipient): ?string
    {
        return DB::transaction(function () use ($batchId, $recipient) {
            if ($recipient->assigned_coupon_code) {
                return $recipient->assigned_coupon_code;
            }

            $assigned = WhatsAppRecipient::whereNotNull('assigned_coupon_code')
                ->pluck('assigned_coupon_code')
                ->toArray();

            $coupon = Coupon::where('batch_id', $batchId)
                ->where('status', 'active')
                ->where('times_used', 0)
                ->whereNotIn('code', $assigned)
                ->lockForUpdate()
                ->first();

            if (!$coupon) return null;

            $recipient->update(['assigned_coupon_code' => $coupon->code]);
            return $coupon->code;
        });
    }

    private function formatDiscount(\App\Models\CouponBatch $batch): string
    {
        return $batch->discount_type === 'percentage'
            ? $batch->discount_value . '%'
            : '$ ' . number_format($batch->discount_value, 0, ',', '.');
    }
}
