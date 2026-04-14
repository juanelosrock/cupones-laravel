<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Models\ApiRequestLog;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiClientController extends Controller
{
    // ── Listado ───────────────────────────────────────────────────────────────

    public function index()
    {
        $clients = ApiClient::with('user')
            ->withCount('requestLogs')
            ->latest()
            ->paginate(20);

        $stats = [
            'total'          => ApiClient::count(),
            'active'         => ApiClient::where('status', 'active')->count(),
            'requests_today' => ApiRequestLog::whereDate('created_at', today())->count(),
            'errors_today'   => ApiRequestLog::whereDate('created_at', today())
                                    ->where('response_code', '>=', 400)->count(),
        ];

        return view('admin.api-clients.index', compact('clients', 'stats'));
    }

    // ── Crear ─────────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.api-clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:150',
            'description'           => 'nullable|string|max:500',
            'environment'           => 'required|in:production,sandbox',
            'rate_limit_per_minute' => 'required|integer|min:1|max:1000',
            'permissions'           => 'required|array|min:1',
            'permissions.*'         => 'in:validate,redeem,customers,legal,*',
            'allowed_ips'           => 'nullable|string',
            'expires_at'            => 'nullable|date|after:today',
        ]);

        $allowedIps = $this->parseIpList($data['allowed_ips'] ?? '');
        $credentials = ApiClient::generateCredentials();

        $client = ApiClient::create([
            'user_id'               => auth()->id(),
            'name'                  => $data['name'],
            'description'           => $data['description'] ?? null,
            'environment'           => $data['environment'],
            'client_id'             => $credentials['client_id'],
            'client_secret'         => $credentials['client_secret_hashed'],
            'allowed_ips'           => $allowedIps ?: null,
            'rate_limit_per_minute' => $data['rate_limit_per_minute'],
            'permissions'           => $data['permissions'],
            'expires_at'            => $data['expires_at'] ?? null,
            'status'                => 'active',
        ]);

        AuditService::log('created', ApiClient::class, $client->id, [], [
            'name' => $client->name,
        ]);

        return redirect()->route('admin.api-clients.show', $client)
            ->with('success', 'Cliente API creado correctamente.')
            ->with('plain_secret', $credentials['client_secret']);
    }

    // ── Detalle ───────────────────────────────────────────────────────────────

    public function show(ApiClient $apiClient)
    {
        $apiClient->load('user');

        $since24h = now()->subHours(24);
        $metrics = [
            'requests_24h'   => $apiClient->requestLogs()->where('created_at', '>=', $since24h)->count(),
            'errors_24h'     => $apiClient->requestLogs()->where('created_at', '>=', $since24h)->where('response_code', '>=', 400)->count(),
            'avg_ms_24h'     => (int) ($apiClient->requestLogs()->where('created_at', '>=', $since24h)->avg('processing_ms') ?? 0),
            'requests_month' => $apiClient->requestLogs()->whereMonth('created_at', now()->month)->count(),
        ];

        $chart = collect(range(6, 0))->map(function ($daysAgo) use ($apiClient) {
            $day = now()->subDays($daysAgo);
            return [
                'label'  => $day->format('d/m'),
                'total'  => $apiClient->requestLogs()->whereDate('created_at', $day)->count(),
                'errors' => $apiClient->requestLogs()->whereDate('created_at', $day)->where('response_code', '>=', 400)->count(),
            ];
        })->values();

        $maxChart = max(1, $chart->max('total'));

        $logs = $apiClient->requestLogs()->latest('created_at')->paginate(25);

        $topEndpoints = $apiClient->requestLogs()
            ->select('endpoint', 'method',
                DB::raw('count(*) as total'),
                DB::raw('round(avg(processing_ms)) as avg_ms'),
                DB::raw('sum(case when response_code >= 400 then 1 else 0 end) as errors'))
            ->groupBy('endpoint', 'method')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return view('admin.api-clients.show', compact(
            'apiClient', 'metrics', 'chart', 'maxChart', 'logs', 'topEndpoints'
        ));
    }

    // ── Editar ────────────────────────────────────────────────────────────────

    public function edit(ApiClient $apiClient)
    {
        return view('admin.api-clients.edit', compact('apiClient'));
    }

    public function update(Request $request, ApiClient $apiClient)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:150',
            'description'           => 'nullable|string|max:500',
            'rate_limit_per_minute' => 'required|integer|min:1|max:1000',
            'permissions'           => 'required|array|min:1',
            'permissions.*'         => 'in:validate,redeem,customers,legal,*',
            'allowed_ips'           => 'nullable|string',
            'expires_at'            => 'nullable|date',
        ]);

        $old = $apiClient->only(['name', 'rate_limit_per_minute', 'permissions']);
        $allowedIps = $this->parseIpList($data['allowed_ips'] ?? '');

        $apiClient->update([
            'name'                  => $data['name'],
            'description'           => $data['description'] ?? null,
            'rate_limit_per_minute' => $data['rate_limit_per_minute'],
            'permissions'           => $data['permissions'],
            'allowed_ips'           => $allowedIps ?: null,
            'expires_at'            => $data['expires_at'] ?? null,
        ]);

        AuditService::log('updated', ApiClient::class, $apiClient->id, $old,
            $apiClient->fresh()->only(['name', 'rate_limit_per_minute', 'permissions']));

        return redirect()->route('admin.api-clients.show', $apiClient)
            ->with('success', 'Configuración actualizada.');
    }

    // ── Rotación de credenciales ──────────────────────────────────────────────

    public function rotate(ApiClient $apiClient)
    {
        if ($apiClient->status === 'revoked') {
            return back()->withErrors(['rotate' => 'No se puede rotar un cliente revocado.']);
        }

        $credentials = ApiClient::generateCredentials();

        $apiClient->update([
            'client_secret' => $credentials['client_secret_hashed'],
        ]);

        AuditService::log('rotated_secret', ApiClient::class, $apiClient->id, [], [
            'rotated_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('admin.api-clients.show', $apiClient)
            ->with('success', 'Secret rotado. Guarda el nuevo valor — no volverá a mostrarse.')
            ->with('plain_secret', $credentials['client_secret'])
            ->with('rotated', true);
    }

    // ── Ciclo de vida ─────────────────────────────────────────────────────────

    public function activate(ApiClient $apiClient)
    {
        if ($apiClient->status === 'revoked') {
            return back()->withErrors(['status' => 'Un cliente revocado no se puede reactivar.']);
        }
        $apiClient->update(['status' => 'active']);
        AuditService::log('activated', ApiClient::class, $apiClient->id, [], ['status' => 'active']);
        return back()->with('success', 'Cliente API activado.');
    }

    public function deactivate(ApiClient $apiClient)
    {
        $apiClient->update(['status' => 'inactive']);
        AuditService::log('deactivated', ApiClient::class, $apiClient->id, [], ['status' => 'inactive']);
        return back()->with('success', 'Cliente desactivado temporalmente. Las peticiones serán rechazadas.');
    }

    public function revoke(ApiClient $apiClient)
    {
        $apiClient->update(['status' => 'revoked']);
        AuditService::log('revoked', ApiClient::class, $apiClient->id, [], ['status' => 'revoked']);
        return back()->with('success', 'Credenciales revocadas permanentemente.');
    }

    // ── Documentación ─────────────────────────────────────────────────────────

    public function docs()
    {
        $baseUrl = rtrim(config('app.url'), '/') . '/api/v1';
        return view('admin.api-clients.docs', compact('baseUrl'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseIpList(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/[\s,\n]+/', $raw))
        ));
    }
}
