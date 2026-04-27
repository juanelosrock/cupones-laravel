<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\WhatsAppOptOut;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function send(string $phone, string $message): array
    {
        if (WhatsAppOptOut::isOptedOut($phone)) {
            return ['success' => false, 'message' => 'Número en lista de opt-out de WhatsApp.'];
        }

        $driver = Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log');

        try {
            return match ($driver) {
                'zenvia' => $this->sendZenvia($phone, $message),
                default  => $this->sendLog($phone, $message),
            };
        } catch (\Throwable $e) {
            Log::error("WhatsApp send failed to {$phone}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendLog(string $phone, string $message): array
    {
        Log::channel('daily')->info("WHATSAPP [LOG DRIVER] To: {$phone} | Message: {$message}");
        return ['success' => true, 'message' => 'WhatsApp registrado en log (modo desarrollo)'];
    }

    private function sendZenvia(string $phone, string $message): array
    {
        $token   = Setting::get('whatsapp_zenvia_token')   ?? config('services.whatsapp.zenvia_token', '');
        $from    = Setting::get('whatsapp_zenvia_from')    ?? config('services.whatsapp.zenvia_from', '');
        $country = Setting::get('whatsapp_zenvia_country') ?? config('services.whatsapp.zenvia_country', '57');

        if (empty($token) || empty($from)) {
            Log::error('WhatsApp Zenvia credentials not configured (token or from number missing)');
            return ['success' => false, 'message' => 'WhatsApp Zenvia: credenciales no configuradas'];
        }

        $to = preg_replace('/\D/', '', $phone);
        if (strlen($to) === 10 && str_starts_with($to, '3')) {
            $to = $country . $to;
        }

        $response = Http::withHeaders([
            'X-API-TOKEN'  => $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.zenvia.com/v2/channels/whatsapp/messages', [
            'from'     => $from,
            'to'       => $to,
            'contents' => [['type' => 'text', 'text' => $message]],
        ]);

        if ($response->successful()) {
            $body = $response->json();
            return ['success' => true, 'message_id' => $body['id'] ?? null, 'provider_response' => $body];
        }

        Log::warning("Zenvia WhatsApp failed to {$to}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'Zenvia WhatsApp error ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
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
