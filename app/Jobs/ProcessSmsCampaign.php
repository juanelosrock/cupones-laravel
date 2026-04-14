<?php
namespace App\Jobs;

use App\Models\Coupon;
use App\Models\SmsCampaign;
use App\Models\SmsRecipient;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessSmsCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 1;

    public function __construct(private SmsCampaign $campaign) {}

    public function handle(SmsService $smsService): void
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

                // ── Flujo normal (sin consentimiento) ──────────────────────
                if (!$this->campaign->send_consent_link) {
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

                    $message = $smsService->renderTemplate($this->campaign->message_template, $vars);
                    $result  = $smsService->send($recipient->phone, $message);

                    $recipient->update([
                        'status'            => $result['success'] ? 'sent' : 'failed',
                        'message_sent'      => $message,
                        'sent_at'           => $result['success'] ? now() : null,
                        'error_message'     => $result['success'] ? null : $result['message'],
                        'provider_response' => json_encode($result),
                    ]);

                // ── Flujo con consentimiento ────────────────────────────────
                } else {
                    // Garantizar token (debería existir desde store(), esto es fallback)
                    $token = $recipient->consent_token;
                    if (!$token) {
                        $token = Str::random(48);
                        $recipient->update(['consent_token' => $token]);
                        $recipient->consent_token = $token;
                    }

                    // Pre-asignar código único (para que esté listo al aceptar)
                    if ($batch && $batch->code_type === 'unique' && !$recipient->assigned_coupon_code) {
                        $this->reserveUniqueCode($batch->id, $recipient);
                    } elseif ($batch && $batch->code_type === 'general') {
                        $recipient->update(['assigned_coupon_code' => $batch->general_code]);
                    }

                    $vars['link'] = rtrim(config('app.url'), '/') . '/autorizar/' . $token;
                    if ($batch) {
                        $vars['discount'] = $this->formatDiscount($batch);
                    }

                    $message = $smsService->renderTemplate($this->campaign->message_template, $vars);
                    $result  = $smsService->send($recipient->phone, $message);

                    $recipient->update([
                        'status'            => $result['success'] ? 'sent' : 'failed',
                        'message_sent'      => $message,
                        'sent_at'           => $result['success'] ? now() : null,
                        'error_message'     => $result['success'] ? null : $result['message'],
                        'provider_response' => json_encode($result),
                    ]);
                }

                if ($recipient->fresh()->status === 'sent') {
                    $this->campaign->increment('sent_count');
                } else {
                    $this->campaign->increment('failed_count');
                }

                usleep(100000); // 100ms entre envíos

            } catch (\Throwable $e) {
                Log::error("SMS campaign #{$this->campaign->id} error for recipient #{$recipient->id}: " . $e->getMessage());
                $recipient->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                $this->campaign->increment('failed_count');
            }
        }

        $this->campaign->update(['status' => 'sent', 'finished_at' => now()]);
    }

    /** Reserva un cupón único para este destinatario (transacción con lock). */
    private function reserveUniqueCode(int $batchId, SmsRecipient $recipient): ?string
    {
        return DB::transaction(function () use ($batchId, $recipient) {
            // Si ya tiene uno asignado, devolverlo
            if ($recipient->assigned_coupon_code) {
                return $recipient->assigned_coupon_code;
            }

            // Buscar cupón disponible que no esté asignado a ningún recipient
            $assigned = SmsRecipient::whereNotNull('assigned_coupon_code')
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
