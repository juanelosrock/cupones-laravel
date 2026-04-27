<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\WhatsAppOptOut;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message.
     *
     * @param string      $phone       Destination phone (10 digits or full intl)
     * @param string      $contentType 'text' | 'template'
     * @param string|null $text        Plain text (when contentType = 'text')
     * @param string|null $templateId  Zenvia template UUID (when contentType = 'template')
     * @param array       $fields      Template variable values e.g. ['1'=>'María','2'=>'PROMO25']
     * @param string|null $externalId  Optional campaign ID for tracking
     */
    public function send(
        string $phone,
        string $contentType  = 'text',
        ?string $text        = null,
        ?string $templateId  = null,
        array $fields        = [],
        ?string $externalId  = null
    ): array {
        if (WhatsAppOptOut::isOptedOut($phone)) {
            return ['success' => false, 'message' => 'Número en lista de opt-out de WhatsApp.'];
        }

        $driver = Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log');

        try {
            return match ($driver) {
                'zenvia' => $this->sendZenvia($phone, $contentType, $text, $templateId, $fields, $externalId),
                default  => $this->sendLog($phone, $contentType, $text, $templateId),
            };
        } catch (\Throwable $e) {
            Log::error("WhatsApp send failed to {$phone}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendLog(string $phone, string $contentType, ?string $text, ?string $templateId): array
    {
        $info = $contentType === 'template'
            ? "template={$templateId}"
            : "text=" . substr($text ?? '', 0, 80);
        Log::channel('daily')->info("WHATSAPP [LOG DRIVER] To: {$phone} | {$info}");
        return ['success' => true, 'message' => 'WhatsApp registrado en log (modo desarrollo)'];
    }

    private function sendZenvia(
        string $phone,
        string $contentType,
        ?string $text,
        ?string $templateId,
        array $fields,
        ?string $externalId
    ): array {
        $token   = Setting::get('whatsapp_zenvia_token')   ?? config('services.whatsapp.zenvia_token', '');
        $from    = Setting::get('whatsapp_zenvia_from')    ?? config('services.whatsapp.zenvia_from', '');
        $country = Setting::get('whatsapp_zenvia_country') ?? config('services.whatsapp.zenvia_country', '57');

        if (empty($token) || empty($from)) {
            Log::error('WhatsApp Zenvia credentials not configured (token or from number missing)');
            return ['success' => false, 'message' => 'WhatsApp Zenvia: credenciales no configuradas'];
        }

        // Normalise destination number (strip non-digits, prepend country code if needed)
        $to = preg_replace('/\D/', '', $phone);
        if (strlen($to) === 10 && str_starts_with($to, '3')) {
            $to = $country . $to;
        }

        // Build contents array per Zenvia spec
        if ($contentType === 'template') {
            if (empty($templateId)) {
                return ['success' => false, 'message' => 'WhatsApp template: templateId requerido'];
            }
            $contents = [[
                'type'       => 'template',
                'templateId' => $templateId,
                'fields'     => (object) $fields,   // must be JSON object, not array
            ]];
        } else {
            if (empty($text)) {
                return ['success' => false, 'message' => 'WhatsApp text: mensaje vacío'];
            }
            $contents = [[
                'type' => 'text',
                'text' => $text,
            ]];
        }

        $payload = [
            'from'     => $from,
            'to'       => $to,
            'contents' => $contents,
        ];

        if ($externalId) {
            $payload['externalId'] = $externalId;
        }

        $response = Http::withHeaders([
            'X-API-TOKEN'  => $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.zenvia.com/v2/channels/whatsapp/messages', $payload);

        Log::info("Zenvia WhatsApp [{$response->status()}] to {$to}: " . $response->body());

        if ($response->successful()) {
            $body = $response->json();
            return ['success' => true, 'message_id' => $body['id'] ?? null, 'provider_response' => $body];
        }

        Log::warning("Zenvia WhatsApp failed to {$to}: HTTP {$response->status()} — " . $response->body());
        return [
            'success' => false,
            'message' => 'Zenvia WhatsApp ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
        ];
    }

    public function renderTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }
        return $template;
    }
}
