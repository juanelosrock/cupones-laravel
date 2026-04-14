<?php
namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Models\ApiRequestLog;
use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
        $clientId = $request->header('X-Client-Id') ?? $request->bearerToken();
        $clientSecret = $request->header('X-Client-Secret');

        if (!$clientId || !$clientSecret) {
            return response()->json([
                'error'   => 'unauthorized',
                'message' => 'Se requieren los headers X-Client-Id y X-Client-Secret.',
            ], 401);
        }

        $client = ApiClient::where('client_id', $clientId)->first();

        if (!$client) {
            AuditService::alert('invalid_api_client', 'medium', "Intento con client_id inválido: {$clientId}", [], $request->ip());
            return response()->json([
                'error'   => 'unauthorized',
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        if (!password_verify($clientSecret, $client->client_secret)) {
            AuditService::alert('invalid_api_client', 'medium', "Secret inválido para cliente: {$clientId}", [], $request->ip());
            return response()->json([
                'error'   => 'unauthorized',
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        if ($client->status === 'revoked') {
            return response()->json([
                'error'   => 'client_revoked',
                'message' => 'Las credenciales de este cliente han sido revocadas.',
            ], 401);
        }

        if ($client->status !== 'active') {
            return response()->json([
                'error'   => 'client_inactive',
                'message' => 'El cliente API está desactivado.',
            ], 401);
        }

        if ($client->expires_at && $client->expires_at->isPast()) {
            return response()->json([
                'error'   => 'client_expired',
                'message' => 'Las credenciales han vencido.',
            ], 401);
        }

        if (!$client->isAllowedIp($request->ip())) {
            AuditService::alert('ip_blocked', 'high', "IP no permitida: {$request->ip()} para cliente {$clientId}", [], $request->ip());
            return response()->json([
                'error'   => 'ip_not_allowed',
                'message' => 'La IP de origen no está autorizada.',
            ], 403);
        }

        $client->update(['last_used_at' => now()]);

        $request->merge(['_api_client' => $client]);
        $request->attributes->set('api_client', $client);

        $startTime = microtime(true);
        $response = $next($request);
        $processingMs = (int)((microtime(true) - $startTime) * 1000);

        ApiRequestLog::create([
            'api_client_id' => $client->id,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'request_hash' => hash('sha256', $request->getContent()),
            'request_body' => $request->except(['password', 'client_secret']),
            'response_code' => $response->status(),
            'processing_ms' => $processingMs,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return $response;
    }
}