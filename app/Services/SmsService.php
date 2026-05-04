<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\SmsOptOut;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): array
    {
        if (SmsOptOut::isOptedOut($phone)) {
            return ['success' => false, 'message' => 'Número en lista de opt-out.'];
        }

        // DB setting takes priority over .env
        $driver = Setting::get('sms_driver') ?? config('services.sms.driver', 'log');

        try {
            return match ($driver) {
                'zenvia'     => $this->sendZenvia($phone, $message),
                'infobip'    => $this->sendInfobip($phone, $message),
                'labsmobile' => $this->sendLabsMobile($phone, $message),
                default      => $this->sendLog($phone, $message),
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

    private function sendZenvia(string $phone, string $message): array
    {
        $token   = Setting::get('sms_zenvia_token')   ?? config('services.sms.zenvia_token');
        $from    = Setting::get('sms_zenvia_from')    ?? config('services.sms.zenvia_from', 'Promocion');
        $country = Setting::get('sms_zenvia_country') ?? config('services.sms.zenvia_country', '57');

        $to = preg_replace('/\D/', '', $phone);
        if (strlen($to) === 10 && str_starts_with($to, '3')) {
            $to = $country . $to;
        }

        $response = Http::withHeaders([
            'X-API-TOKEN'  => $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.zenvia.com/v2/channels/sms/messages', [
            'from'     => $from,
            'to'       => $to,
            'contents' => [['type' => 'text', 'text' => $message]],
        ]);

        if ($response->successful()) {
            $body = $response->json();
            return ['success' => true, 'message_id' => $body['id'] ?? null, 'provider_response' => $body];
        }

        Log::warning("Zenvia SMS failed to {$to}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'Zenvia error ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
        ];
    }

    private function sendInfobip(string $phone, string $message): array
    {
        $apiKey  = Setting::get('sms_infobip_api_key')  ?? config('services.sms.infobip_api_key');
        $baseUrl = Setting::get('sms_infobip_base_url') ?? config('services.sms.infobip_base_url', 'https://api.infobip.com');
        $from    = Setting::get('sms_infobip_from')     ?? config('services.sms.from', 'Promocion');

        $baseUrl = rtrim($baseUrl, '/');

        $response = Http::withHeaders([
            'Authorization' => "App {$apiKey}",
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post("{$baseUrl}/sms/3/messages", [
            'messages' => [[
                'sender'       => $from,
                'destinations' => [['to' => preg_replace('/\D/', '', $phone)]],
                'content'      => ['text' => $message],
            ]],
        ]);

        if ($response->successful()) {
            $body   = $response->json();
            $msgId  = $body['messages'][0]['messageId'] ?? null;
            $status = $body['messages'][0]['status']['groupName'] ?? null;

            if (!in_array($status, ['REJECTED', 'UNDELIVERABLE'])) {
                return ['success' => true, 'message_id' => $msgId, 'provider_response' => $body];
            }

            $errMsg = $body['messages'][0]['status']['description'] ?? 'Error desconocido';
            Log::warning("Infobip SMS failed to {$phone}: {$errMsg}");
            return ['success' => false, 'message' => "Infobip: {$errMsg}"];
        }

        Log::warning("Infobip SMS failed to {$phone}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'Infobip error ' . $response->status() . ': ' . ($response->json('requestError.serviceException.text') ?? $response->body()),
        ];
    }

    private function sendLabsMobile(string $phone, string $message): array
    {
        $username = Setting::get('sms_labsmobile_username') ?? config('services.sms.labsmobile_username', '');
        $token    = Setting::get('sms_labsmobile_token')    ?? config('services.sms.labsmobile_token', '');
        $tpoa     = Setting::get('sms_labsmobile_tpoa')     ?? config('services.sms.labsmobile_tpoa', 'Promocion');
        $country  = Setting::get('sms_labsmobile_country')  ?? config('services.sms.labsmobile_country', '57');

        if (empty($username) || empty($token)) {
            Log::error('LabsMobile credentials not configured (username or token missing)');
            return ['success' => false, 'message' => 'LabsMobile: credenciales no configuradas'];
        }

        $to = preg_replace('/\D/', '', $phone);
        if (strlen($to) === 10 && str_starts_with($to, '3')) {
            $to = $country . $to;
        }

        $response = Http::withBasicAuth((string) $username, (string) $token)
            ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
            ->post('https://api.labsmobile.com/json/send', [
                'message'   => $message,
                'tpoa'      => $tpoa,
                'recipient' => [['msisdn' => $to]],
            ]);

        Log::info("LabsMobile response [{$response->status()}]: " . $response->body());

        if ($response->successful()) {
            $body = $response->json();
            $code = (string) ($body['code'] ?? '1');   // normalise int/string
            if ($code === '0') {
                return ['success' => true, 'message_id' => $body['subid'] ?? null, 'provider_response' => $body];
            }
            $errMsg = $body['message'] ?? 'Error desconocido';
            Log::warning("LabsMobile SMS failed to {$to}: {$errMsg} (code {$code})");
            return ['success' => false, 'message' => "LabsMobile [{$code}]: {$errMsg}"];
        }

        Log::warning("LabsMobile SMS failed to {$to}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'LabsMobile error ' . $response->status() . ': ' . $response->body(),
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
