<?php
namespace App\Services;

use App\Models\EmailOptOut;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function send(string $to, string $subject, string $htmlBody, ?string $fromName = null, ?string $fromEmail = null): array
    {
        $to = strtolower(trim($to));

        if (EmailOptOut::isOptedOut($to)) {
            return ['success' => false, 'message' => 'Correo en lista de opt-out.'];
        }

        $driver = config('services.email.driver', 'log');

        try {
            return match ($driver) {
                'zenvia' => $this->sendZenvia($to, $subject, $htmlBody, $fromName, $fromEmail),
                default  => $this->sendLog($to, $subject, $htmlBody),
            };
        } catch (\Throwable $e) {
            Log::error("Email send failed to {$to}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendLog(string $to, string $subject, string $htmlBody): array
    {
        Log::channel('daily')->info("EMAIL [LOG DRIVER] To: {$to} | Subject: {$subject}");
        return ['success' => true, 'message' => 'Email registrado en log (modo desarrollo)'];
    }

    private function sendZenvia(string $to, string $subject, string $htmlBody, ?string $fromName, ?string $fromEmail): array
    {
        $token     = config('services.email.zenvia_token');
        $fromName  = $fromName  ?? config('services.email.zenvia_from_name', 'CuponesHub');
        $fromEmail = $fromEmail ?? config('services.email.zenvia_from_address');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'X-API-TOKEN' => $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.zenvia.com/v2/channels/email/messages', [
            'from'     => $fromEmail,
            'to'       => $to,
            'contents' => [
                [
                    'type'    => 'email',
                    'subject' => $subject,
                    'html'    => $htmlBody,
                ],
            ],
        ]);

        if ($response->successful()) {
            $body = $response->json();
            return [
                'success'           => true,
                'message_id'        => $body['id'] ?? null,
                'provider_response' => $body,
            ];
        }

        Log::warning("Zenvia Email failed to {$to}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'Zenvia error ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
        ];
    }

    public function renderTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
}
