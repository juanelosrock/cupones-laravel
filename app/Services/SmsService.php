<?php
namespace App\Services;

use App\Models\SmsOptOut;
use App\Models\SmsRecipient;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): array
    {
        if (SmsOptOut::isOptedOut($phone)) {
            return ['success' => false, 'message' => 'Número en lista de opt-out.'];
        }

        $driver = config('services.sms.driver', 'log');

        try {
            return match($driver) {
                'log'     => $this->sendLog($phone, $message),
                'infobip' => $this->sendInfobip($phone, $message),
                'twilio'  => $this->sendTwilio($phone, $message),
                'zenvia'  => $this->sendZenvia($phone, $message),
                default   => $this->sendLog($phone, $message),
            };
        } catch (\Throwable $e) {
            Log::error("SMS send failed to {$phone}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendLog(string $phone, string $message): array
    {
        Log::channel('daily')->info("SMS [LOG DRIVER] To: {$phone} | Message: {$message}");
        return ['success' => true, 'message' => 'SMS registrado en log (modo desarrollo)'];
    }

    private function sendInfobip(string $phone, string $message): array
    {
        $apiKey = config('services.sms.infobip_api_key');
        $baseUrl = config('services.sms.infobip_base_url');
        $from = config('services.sms.from', 'CuponesHub');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "App {$apiKey}",
            'Content-Type' => 'application/json',
        ])->post("{$baseUrl}/sms/2/text/advanced", [
            'messages' => [[
                'from' => $from,
                'destinations' => [['to' => $phone]],
                'text' => $message,
            ]],
        ]);

        if ($response->successful()) {
            return ['success' => true, 'provider_response' => $response->json()];
        }

        return ['success' => false, 'message' => 'Infobip error: ' . $response->status()];
    }

    private function sendTwilio(string $phone, string $message): array
    {
        // Implementación Twilio (requiere SDK o HTTP)
        return ['success' => false, 'message' => 'Twilio driver no implementado.'];
    }

    private function sendZenvia(string $phone, string $message): array
    {
        $token   = config('services.sms.zenvia_token');
        $from    = config('services.sms.zenvia_from', 'CuponesHub');
        $country = config('services.sms.zenvia_country', '57');

        // Normalizar número al formato E.164 sin "+" (ej. 573001234567)
        $to = preg_replace('/\D/', '', $phone);
        if (strlen($to) === 10 && str_starts_with($to, '3')) {
            $to = $country . $to; // Colombia: 57 + 10 dígitos
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'X-API-TOKEN' => $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.zenvia.com/v2/channels/sms/messages', [
            'from'     => $from,
            'to'       => $to,
            'contents' => [
                ['type' => 'text', 'text' => $message],
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

        Log::warning("Zenvia SMS failed to {$to}: HTTP {$response->status()} — " . $response->body());
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