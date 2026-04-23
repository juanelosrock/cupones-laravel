<?php
namespace App\Jobs;

use App\Models\Coupon;
use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 1;

    public function __construct(private EmailCampaign $campaign) {}

    public function handle(EmailService $emailService): void
    {
        $this->campaign->update(['status' => 'sending', 'started_at' => now()]);

        $recipients = $this->campaign->recipients()->where('status', 'pending')->get();
        $batch      = $this->campaign->couponBatch;

        foreach ($recipients as $recipient) {
            try {
                $vars = [
                    'name'  => $recipient->customer?->name ?? 'Cliente',
                    'email' => $recipient->email,
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

                $subject  = $emailService->renderTemplate($this->campaign->subject, $vars);
                $htmlBody = $emailService->renderTemplate($this->campaign->message_template, $vars);

                $result = $emailService->send(
                    $recipient->email,
                    $subject,
                    $htmlBody,
                    $this->campaign->from_name,
                    $this->campaign->from_email
                );

                $recipient->update([
                    'status'            => $result['success'] ? 'sent' : 'failed',
                    'subject_sent'      => $subject,
                    'message_sent'      => $htmlBody,
                    'sent_at'           => $result['success'] ? now() : null,
                    'error_message'     => $result['success'] ? null : $result['message'],
                    'provider_response' => json_encode($result['provider_response'] ?? $result),
                ]);

                if ($result['success']) {
                    $this->campaign->increment('sent_count');
                } else {
                    $this->campaign->increment('failed_count');
                }

                usleep(100000); // 100ms entre envíos

            } catch (\Throwable $e) {
                Log::error("Email campaign #{$this->campaign->id} error for recipient #{$recipient->id}: " . $e->getMessage());
                $recipient->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $this->campaign->increment('failed_count');
            }
        }

        $this->campaign->update(['status' => 'sent', 'finished_at' => now()]);
    }

    private function reserveUniqueCode(int $batchId, EmailRecipient $recipient): ?string
    {
        return DB::transaction(function () use ($batchId, $recipient) {
            $assigned = EmailRecipient::whereNotNull('assigned_coupon_code')
                ->pluck('assigned_coupon_code')
                ->toArray();

            $coupon = Coupon::where('batch_id', $batchId)
                ->where('status', 'active')
                ->where('times_used', 0)
                ->whereNotIn('code', $assigned)
                ->lockForUpdate()
                ->first();

            if (!$coupon) {
                return null;
            }

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
