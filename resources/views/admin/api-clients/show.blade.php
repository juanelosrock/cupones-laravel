@extends('layouts.admin')
@section('title', $apiClient->name . ' — API Client')
@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex items-start justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.api-clients.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← API Clients</a>
        <span class="text-gray-300">/</span>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-900">{{ $apiClient->name }}</h1>
                @if(($apiClient->environment ?? 'production') === 'sandbox')
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Sandbox</span>
                @else
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Producción</span>
                @endif
                @php
                    $sc = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-500', 'revoked' => 'bg-red-100 text-red-700'];
                    $sl = ['active' => 'Activo', 'inactive' => 'Inactivo', 'revoked' => 'Revocado'];
                @endphp
                <span class="text-xs px-2 py-0.5 rounded-full {{ $sc[$apiClient->status] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ $sl[$apiClient->status] ?? $apiClient->status }}
                </span>
            </div>
            @if($apiClient->description)
            <p class="text-sm text-gray-500 mt-0.5">{{ $apiClient->description }}</p>
            @endif
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.api-clients.edit', $apiClient) }}"
           class="flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
    </div>
</div>

{{-- One-time secret banner --}}
@if(session('plain_secret'))
<div class="mb-5 rounded-xl border-2 border-amber-400 bg-amber-50 p-4" x-data="{ copied: false, shown: true }" x-show="shown">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm font-bold text-amber-800">
                    @if(session('rotated'))
                    Secret rotado — guárdalo ahora, no volverá a mostrarse
                    @else
                    Credenciales generadas — guárdalas ahora, el secret no volverá a mostrarse
                    @endif
                </p>
            </div>
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-amber-700 w-28 flex-shrink-0">Client ID</span>
                    <code class="flex-1 text-xs font-mono bg-white border border-amber-200 rounded px-2 py-1 text-gray-800 select-all">{{ $apiClient->client_id }}</code>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-amber-700 w-28 flex-shrink-0">Client Secret</span>
                    <code class="flex-1 text-xs font-mono bg-white border border-amber-200 rounded px-2 py-1 text-red-700 font-bold select-all" id="plain-secret">{{ session('plain_secret') }}</code>
                    <button @click="navigator.clipboard.writeText('{{ session('plain_secret') }}'); copied=true; setTimeout(()=>copied=false,2000)"
                            class="flex items-center gap-1 text-xs px-2 py-1 bg-amber-200 hover:bg-amber-300 text-amber-800 rounded transition-colors flex-shrink-0">
                        <span x-show="!copied">Copiar</span>
                        <span x-show="copied">✓ Copiado</span>
                    </button>
                </div>
            </div>
        </div>
        <button @click="shown=false" class="text-amber-400 hover:text-amber-600 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('success') && !session('plain_secret'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-3 gap-5">

    {{-- Columna izquierda: credenciales + permisos + acciones --}}
    <div class="col-span-1 space-y-5">

        {{-- Credenciales --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Credenciales
            </h2>
            <div class="space-y-3">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Client ID</p>
                    <div class="flex items-center gap-2" x-data="{ copied: false }">
                        <code class="flex-1 text-xs font-mono bg-gray-50 border border-gray-100 rounded px-2 py-1.5 text-gray-700 break-all">{{ $apiClient->client_id }}</code>
                        <button @click="navigator.clipboard.writeText('{{ $apiClient->client_id }}'); copied=true; setTimeout(()=>copied=false,2000)"
                                class="text-xs px-1.5 py-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors flex-shrink-0" title="Copiar">
                            <span x-show="!copied">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </span>
                            <span x-show="copied" class="text-green-500">✓</span>
                        </button>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Client Secret</p>
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded px-2 py-1.5">
                        <code class="flex-1 text-xs font-mono text-gray-400 tracking-widest">••••••••••••••••••••••••</code>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Hasheado. Solo visible al generar/rotar.</p>
                </div>
            </div>

            {{-- Rotar secret --}}
            @if($apiClient->status !== 'revoked')
            <div class="mt-4 pt-4 border-t border-gray-100" x-data="{ confirm: false }">
                <div x-show="!confirm">
                    <button @click="confirm=true"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 border border-orange-200 text-orange-600 hover:bg-orange-50 rounded-lg text-xs font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Rotar Secret
                    </button>
                </div>
                <div x-show="confirm" class="space-y-2">
                    <p class="text-xs text-orange-700 bg-orange-50 border border-orange-200 rounded p-2">
                        El secret actual quedará inválido inmediatamente. Todos los sistemas que lo usen dejarán de funcionar.
                    </p>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.api-clients.rotate', $apiClient) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-xs font-semibold transition-colors">
                                Confirmar rotación
                            </button>
                        </form>
                        <button @click="confirm=false" class="px-3 py-1.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-xs transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Permisos --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Permisos (Scopes)</h2>
            <div class="flex flex-wrap gap-1.5">
                @forelse($apiClient->permissions ?? [] as $perm)
                <span class="text-xs px-2 py-1 rounded-lg bg-blue-50 text-blue-700 font-mono border border-blue-100">
                    {{ $perm === '*' ? 'all (wildcard)' : $perm }}
                </span>
                @empty
                <span class="text-xs text-gray-400">Sin permisos asignados</span>
                @endforelse
            </div>
        </div>

        {{-- Configuración --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Configuración</h2>
            <dl class="space-y-2.5">
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-500">Rate limit</dt>
                    <dd class="text-xs font-medium text-gray-800">{{ $apiClient->rate_limit_per_minute }} req/min</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-500">Entorno</dt>
                    <dd class="text-xs font-medium text-gray-800 capitalize">{{ $apiClient->environment ?? 'production' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-500">Vencimiento</dt>
                    <dd class="text-xs font-medium {{ $apiClient->expires_at?->isPast() ? 'text-red-500' : 'text-gray-800' }}">
                        {{ $apiClient->expires_at?->format('d/m/Y') ?? '—' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-500">Último uso</dt>
                    <dd class="text-xs font-medium text-gray-800">{{ $apiClient->last_used_at?->diffForHumans() ?? 'Nunca' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-500">Creado</dt>
                    <dd class="text-xs font-medium text-gray-800">{{ $apiClient->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($apiClient->user)
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-500">Creado por</dt>
                    <dd class="text-xs font-medium text-gray-800">{{ $apiClient->user->name }}</dd>
                </div>
                @endif
            </dl>

            @if(!empty($apiClient->allowed_ips))
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-1.5">IPs permitidas</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($apiClient->allowed_ips as $ip)
                    <span class="text-[10px] font-mono px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">{{ $ip }}</span>
                    @endforeach
                </div>
            </div>
            @else
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-400">Acepta cualquier IP</p>
            </div>
            @endif
        </div>

        {{-- Ciclo de vida --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Estado del cliente</h2>
            <div class="space-y-2" x-data="{ confirmRevoke: false }">
                @if($apiClient->status === 'active')
                <form method="POST" action="{{ route('admin.api-clients.deactivate', $apiClient) }}">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-xs font-medium transition-colors text-left flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Desactivar temporalmente
                    </button>
                </form>
                @elseif($apiClient->status === 'inactive')
                <form method="POST" action="{{ route('admin.api-clients.activate', $apiClient) }}">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 border border-green-200 text-green-600 hover:bg-green-50 rounded-lg text-xs font-medium transition-colors flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Reactivar
                    </button>
                </form>
                @endif

                @if($apiClient->status !== 'revoked')
                <div x-show="!confirmRevoke">
                    <button @click="confirmRevoke=true"
                            class="w-full px-3 py-2 border border-red-200 text-red-600 hover:bg-red-50 rounded-lg text-xs font-medium transition-colors flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Revocar permanentemente
                    </button>
                </div>
                <div x-show="confirmRevoke" class="space-y-2">
                    <p class="text-xs text-red-700 bg-red-50 border border-red-200 rounded p-2">
                        Acción irreversible. Las credenciales quedarán inútiles para siempre.
                    </p>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('admin.api-clients.revoke', $apiClient) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-semibold">
                                Sí, revocar
                            </button>
                        </form>
                        <button @click="confirmRevoke=false" class="px-3 py-1.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg text-xs">
                            Cancelar
                        </button>
                    </div>
                </div>
                @else
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-xs text-red-700 font-medium">Credenciales revocadas permanentemente</p>
                    <p class="text-xs text-red-500 mt-0.5">Revocado {{ $apiClient->updated_at->diffForHumans() }}</p>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Columna derecha: métricas + gráfica + logs --}}
    <div class="col-span-2 space-y-5">

        {{-- Métricas 24h --}}
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Requests 24h</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['requests_24h']) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">peticiones totales</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Errores 24h</p>
                <p class="text-2xl font-bold {{ $metrics['errors_24h'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ number_format($metrics['errors_24h']) }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">respuestas 4xx/5xx</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Latencia media</p>
                <p class="text-2xl font-bold text-gray-900">{{ $metrics['avg_ms_24h'] }}<span class="text-sm font-normal text-gray-400 ml-1">ms</span></p>
                <p class="text-xs text-gray-400 mt-0.5">últimas 24 horas</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Requests mes</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['requests_month']) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('F Y') }}</p>
            </div>
        </div>

        {{-- Gráfica 7 días --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Actividad — últimos 7 días</h2>
            @if($chart->sum('total') === 0)
            <div class="flex items-center justify-center h-24 text-sm text-gray-400">Sin actividad en los últimos 7 días</div>
            @else
            <div class="flex items-end gap-2 h-28">
                @foreach($chart as $day)
                @php $heightPct = $maxChart > 0 ? round(($day['total'] / $maxChart) * 100) : 0; @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    <p class="text-[9px] text-gray-400">{{ $day['total'] > 0 ? number_format($day['total']) : '' }}</p>
                    <div class="w-full relative flex flex-col justify-end" style="height: 80px;">
                        {{-- Total bar --}}
                        <div class="w-full bg-blue-100 rounded-sm relative overflow-hidden" style="height: {{ max(4, $heightPct) }}%">
                            {{-- Error overlay --}}
                            @if($day['errors'] > 0 && $day['total'] > 0)
                            <div class="absolute bottom-0 left-0 right-0 bg-red-400 rounded-sm"
                                 style="height: {{ round(($day['errors'] / $day['total']) * 100) }}%"></div>
                            @endif
                        </div>
                    </div>
                    <p class="text-[9px] text-gray-400">{{ $day['label'] }}</p>
                </div>
                @endforeach
            </div>
            <div class="flex items-center gap-3 mt-2">
                <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-sm bg-blue-100"></div><span class="text-[10px] text-gray-400">Requests</span></div>
                <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-sm bg-red-400"></div><span class="text-[10px] text-gray-400">Errores</span></div>
            </div>
            @endif
        </div>

        {{-- Top Endpoints --}}
        @if($topEndpoints->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b">
                <h2 class="text-sm font-semibold text-gray-800">Top endpoints</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                            <th class="px-5 py-2.5 text-left">Endpoint</th>
                            <th class="px-5 py-2.5 text-right">Calls</th>
                            <th class="px-5 py-2.5 text-right">Errores</th>
                            <th class="px-5 py-2.5 text-right">Latencia media</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($topEndpoints as $ep)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-2.5">
                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded mr-1.5
                                    {{ in_array($ep->method, ['POST','PUT','PATCH']) ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $ep->method }}
                                </span>
                                <span class="font-mono text-gray-700">{{ $ep->endpoint }}</span>
                            </td>
                            <td class="px-5 py-2.5 text-right text-gray-700">{{ number_format($ep->total) }}</td>
                            <td class="px-5 py-2.5 text-right {{ $ep->errors > 0 ? 'text-red-500' : 'text-gray-400' }}">
                                {{ $ep->errors > 0 ? number_format($ep->errors) : '—' }}
                            </td>
                            <td class="px-5 py-2.5 text-right text-gray-600">{{ $ep->avg_ms }} ms</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Request Logs --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800">Log de peticiones</h2>
                <p class="text-xs text-gray-400">{{ $logs->total() }} registros</p>
            </div>
            @if($logs->isEmpty())
            <div class="px-5 py-10 text-center text-sm text-gray-400">Sin peticiones registradas aún</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                            <th class="px-4 py-2.5 text-left">Endpoint</th>
                            <th class="px-4 py-2.5 text-left">Código</th>
                            <th class="px-4 py-2.5 text-right">ms</th>
                            <th class="px-4 py-2.5 text-left">IP</th>
                            <th class="px-4 py-2.5 text-left">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono">
                                <span class="text-[10px] font-mono px-1 py-0.5 rounded mr-1
                                    {{ in_array($log->method, ['POST','PUT','PATCH']) ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600' }}">
                                    {{ $log->method }}
                                </span>
                                {{ $log->endpoint }}
                            </td>
                            <td class="px-4 py-2">
                                @php $code = $log->response_code; @endphp
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-mono
                                    {{ $code < 300 ? 'bg-green-100 text-green-700' : ($code < 400 ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $code }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-gray-500">{{ $log->processing_ms ?? '—' }}</td>
                            <td class="px-4 py-2 font-mono text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t">{{ $logs->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
