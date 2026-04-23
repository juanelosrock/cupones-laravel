<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Services\EmailService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotifyController extends Controller
{
    public function __construct(
        private SmsService   $smsService,
        private EmailService $emailService,
    ) {}

    /**
     * POST /api/v1/notify/send
     *
     * Envía simultáneamente un SMS y un email vía Zenvia.
     * Retorna siempre HTTP 200 — verificar "sms.status" y "email.status".
     */
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'          => 'required|string|max:20',
            'email'          => 'required|email|max:150',
            'sms_text'       => 'required|string|max:160',
            'email_subject'  => 'required|string|max:255',
            'email_template' => 'required|string',
        ]);

        /** @var \App\Models\ApiClient $client */
        $client = $request->attributes->get('api_client');

        // ── SMS ───────────────────────────────────────────────────────────────
        $smsResult = $this->smsService->send($data['phone'], $data['sms_text']);

        // ── Email ─────────────────────────────────────────────────────────────
        $emailResult = $this->emailService->send(
            $data['email'],
            $data['email_subject'],
            $data['email_template']
        );

        // ── Persistir log ─────────────────────────────────────────────────────
        NotificationLog::create([
            'api_client_id'   => $client->id,
            'phone'           => $data['phone'],
            'email'           => $data['email'],
            'email_subject'   => $data['email_subject'],
            'sms_status'      => $smsResult['success']   ? 'sent' : 'failed',
            'email_status'    => $emailResult['success'] ? 'sent' : 'failed',
            'sms_message_id'  => $smsResult['message_id']   ?? null,
            'email_message_id'=> $emailResult['message_id'] ?? null,
            'sms_error'       => $smsResult['success']   ? null : ($smsResult['message']   ?? null),
            'email_error'     => $emailResult['success'] ? null : ($emailResult['message'] ?? null),
            'ip_address'      => $request->ip(),
        ]);

        $overallSuccess = $smsResult['success'] && $emailResult['success'];

        return response()->json([
            'success' => $overallSuccess,
            'sms' => [
                'status'     => $smsResult['success'] ? 'sent' : 'failed',
                'message_id' => $smsResult['message_id'] ?? null,
                'error'      => $smsResult['success'] ? null : ($smsResult['message'] ?? null),
            ],
            'email' => [
                'status'     => $emailResult['success'] ? 'sent' : 'failed',
                'message_id' => $emailResult['message_id'] ?? null,
                'error'      => $emailResult['success'] ? null : ($emailResult['message'] ?? null),
            ],
            'meta' => [
                'request_id'   => (string) Str::uuid(),
                'processed_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
