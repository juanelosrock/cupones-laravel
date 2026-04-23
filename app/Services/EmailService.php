<?php
namespace App\Services;

use App\Models\EmailOptOut;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function send(string $to, string $subject, string $htmlBody, ?string $fromName = null, ?string $fromEmail = null): array
    {
        $to = strtolower(trim($to));

        if (EmailOptOut::isOptedOut($to)) {
            return ['success' => false, 'message' => 'Correo en lista de opt-out.'];
        }

        // DB setting takes priority over .env
        $driver = Setting::get('email_driver') ?? config('services.email.driver', 'log');

        try {
            return match ($driver) {
                'zenvia'  => $this->sendZenvia($to, $subject, $htmlBody, $fromName, $fromEmail),
                'infobip' => $this->sendInfobip($to, $subject, $htmlBody, $fromName, $fromEmail),
                default   => $this->sendLog($to, $subject),
            };
        } catch (\Throwable $e) {
            Log::error("Email send failed to {$to}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendLog(string $to, string $subject): array
    {
        Log::channel('daily')->info("EMAIL [LOG DRIVER] To: {$to} | Subject: {$subject}");
        return ['success' => true, 'message' => 'Email registrado en log (modo desarrollo)'];
    }

    private function sendZenvia(string $to, string $subject, string $htmlBody, ?string $fromName, ?string $fromEmail): array
    {
        $token     = Setting::get('email_zenvia_token')        ?? config('services.email.zenvia_token');
        $fromEmail = $fromEmail
            ?? Setting::get('email_zenvia_from_address') ?? config('services.email.zenvia_from_address');

        $response = Http::withHeaders([
            'X-API-TOKEN'  => $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.zenvia.com/v2/channels/email/messages', [
            'from'     => $fromEmail,
            'to'       => $to,
            'contents' => [
                ['type' => 'email', 'subject' => $subject, 'html' => $htmlBody],
            ],
        ]);

        if ($response->successful()) {
            $body = $response->json();
            return ['success' => true, 'message_id' => $body['id'] ?? null, 'provider_response' => $body];
        }

        Log::warning("Zenvia Email failed to {$to}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'Zenvia error ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
        ];
    }

    private function sendInfobip(string $to, string $subject, string $htmlBody, ?string $fromName, ?string $fromEmail): array
    {
        $apiKey    = Setting::get('email_infobip_api_key')      ?? config('services.email.infobip_api_key');
        $baseUrl   = Setting::get('email_infobip_base_url')     ?? config('services.email.infobip_base_url', 'https://api.infobip.com');
        $fromEmail = $fromEmail
            ?? Setting::get('email_infobip_from_address') ?? config('services.email.infobip_from_address');
        $fromName  = $fromName
            ?? Setting::get('email_infobip_from_name')    ?? config('services.email.infobip_from_name', 'CuponesHub');

        $baseUrl = rtrim($baseUrl, '/');

        $response = Http::withHeaders([
            'Authorization' => "App {$apiKey}",
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post("{$baseUrl}/email/4/messages", [
            'messages' => [[
                'sender'       => "{$fromName} <{$fromEmail}>",
                'destinations' => [['to' => [['destination' => $to]]]],
                'content'      => [
                    'subject' => $subject,
                    'html'    => $htmlBody,
                ],
            ]],
        ]);

        if ($response->successful()) {
            $body  = $response->json();
            $msgId = $body['messages'][0]['messageId'] ?? null;
            return ['success' => true, 'message_id' => $msgId, 'provider_response' => $body];
        }

        Log::warning("Infobip Email failed to {$to}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'Infobip error ' . $response->status() . ': ' . ($response->json('requestError.serviceException.text') ?? $response->body()),
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
