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

    /**
     * Create a new WhatsApp template in Zenvia and submit it to Meta for approval.
     */
    public function createTemplate(array $data): array
    {
        $driver = Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log');
        if ($driver !== 'zenvia') {
            return ['success' => false, 'message' => 'Driver no es Zenvia. Configura las credenciales en Proveedores.'];
        }

        $token  = Setting::get('whatsapp_zenvia_token') ?? config('services.whatsapp.zenvia_token', '');
        $from   = Setting::get('whatsapp_zenvia_from')  ?? config('services.whatsapp.zenvia_from', '');

        if (empty($token) || empty($from)) {
            return ['success' => false, 'message' => 'Credenciales Zenvia no configuradas (token o número origen).'];
        }

        $payload = [
            'name'       => $data['name'],
            'locale'     => $data['locale'],
            'channel'    => 'WHATSAPP',
            'senderId'   => $from,
            'category'   => $data['category'],
            'components' => $data['components'],
        ];

        try {
            Log::info("Zenvia createTemplate payload: " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $response = Http::withHeaders([
                'X-API-TOKEN'  => $token,
                'Content-Type' => 'application/json',
            ])->post('https://api.zenvia.com/v2/templates', $payload);

            Log::info("Zenvia createTemplate [{$response->status()}]: " . $response->body());

            if ($response->successful()) {
                return ['success' => true, 'template' => $response->json()];
            }

            $error = $response->json('message') ?? $response->body();
            return ['success' => false, 'message' => "Zenvia {$response->status()}: {$error}"];
        } catch (\Throwable $e) {
            Log::error('Zenvia createTemplate exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete a WhatsApp template from Zenvia.
     */
    public function deleteTemplate(string $templateId): array
    {
        $driver = Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log');
        if ($driver !== 'zenvia') {
            return ['success' => false, 'message' => 'Driver no es Zenvia.'];
        }

        $token = Setting::get('whatsapp_zenvia_token') ?? config('services.whatsapp.zenvia_token', '');
        if (empty($token)) {
            return ['success' => false, 'message' => 'Token Zenvia no configurado.'];
        }

        try {
            $response = Http::withHeaders(['X-API-TOKEN' => $token])
                ->delete("https://api.zenvia.com/v2/templates/{$templateId}");

            Log::info("Zenvia deleteTemplate/{$templateId} [{$response->status()}]");

            if ($response->successful() || $response->status() === 204) {
                return ['success' => true];
            }

            $error = $response->json('message') ?? $response->body();
            return ['success' => false, 'message' => "Zenvia {$response->status()}: {$error}"];
        } catch (\Throwable $e) {
            Log::error("Zenvia deleteTemplate/{$templateId} exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fetch WhatsApp templates registered in the Zenvia account.
     * Returns [] when driver is not zenvia or credentials are missing.
     */
    public function getTemplates(): array
    {
        $driver = Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log');
        if ($driver !== 'zenvia') {
            return [];
        }

        $token = Setting::get('whatsapp_zenvia_token') ?? config('services.whatsapp.zenvia_token', '');
        if (empty($token)) {
            return [];
        }

        try {
            $response = Http::withHeaders(['X-API-TOKEN' => $token])
                ->get('https://api.zenvia.com/v2/templates', ['channel' => 'whatsapp']);

            if (!$response->successful()) {
                Log::warning('Zenvia getTemplates failed: HTTP ' . $response->status() . ' — ' . $response->body());
                return [];
            }

            return $response->json('templates', []);
        } catch (\Throwable $e) {
            Log::error('Zenvia getTemplates exception: ' . $e->getMessage());
            return [];
        }
    }

    public function renderTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }
        return $template;
    }
}
