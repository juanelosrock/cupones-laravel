@extends('layouts.admin')
@section('title', 'API Clients')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">API Clients</h1>
        <p class="text-sm text-gray-500 mt-0.5">Gestiona las credenciales de acceso para sistemas externos</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.api-clients.docs') }}"
           class="flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Documentación API
        </a>
        <a href="{{ route('admin.api-clients.create') }}"
           class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Cliente API
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
@endif

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Clientes API</p>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        <p class="text-xs text-green-600 mt-0.5">{{ $stats['active'] }} activos</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Requests hoy</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['requests_today']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">peticiones totales</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Errores hoy</p>
        <p class="text-2xl font-bold {{ $stats['errors_today'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
            {{ number_format($stats['errors_today']) }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">respuestas 4xx / 5xx</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Tasa de éxito hoy</p>
        @php
            $rate = $stats['requests_today'] > 0
                ? round((1 - $stats['errors_today'] / $stats['requests_today']) * 100, 1)
                : 100;
        @endphp
        <p class="text-2xl font-bold text-gray-900">{{ $rate }}%</p>
        <p class="text-xs text-gray-400 mt-0.5">requests exitosos</p>
    </div>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800">Clientes registrados</h2>
        <p class="text-xs text-gray-400">{{ $clients->total() }} en total</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide border-b">
                    <th class="px-5 py-3 text-left">Nombre / Client ID</th>
                    <th class="px-5 py-3 text-left">Entorno</th>
                    <th class="px-5 py-3 text-left">Permisos</th>
                    <th class="px-5 py-3 text-left">Rate limit</th>
                    <th class="px-5 py-3 text-left">Requests</th>
                    <th class="px-5 py-3 text-left">Último uso</th>
                    <th class="px-5 py-3 text-left">Vence</th>
                    <th class="px-5 py-3 text-left">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                    <p class="font-medium text-gray-900">{{ $client->name }}</p>
                    <code class="text-[10px] font-mono text-gray-400">{{ Str::limit($client->client_id, 30) }}</code>
                </td>
                <td class="px-5 py-3">
                    @if(($client->environment ?? 'production') === 'sandbox')
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Sandbox</span>
                    @else
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Producción</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <div class="flex flex-wrap gap-1">
                        @foreach($client->permissions ?? [] as $perm)
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono">
                            {{ $perm === '*' ? 'all' : $perm }}
                        </span>
                        @endforeach
                    </div>
                </td>
                <td class="px-5 py-3 text-xs text-gray-600">{{ $client->rate_limit_per_minute }} req/min</td>
                <td class="px-5 py-3 text-xs text-gray-600">{{ number_format($client->request_logs_count) }}</td>
                <td class="px-5 py-3 text-xs text-gray-400">{{ $client->last_used_at?->diffForHumans() ?? 'Nunca' }}</td>
                <td class="px-5 py-3 text-xs">
                    @if($client->expires_at)
                    <span class="{{ $client->expires_at->isPast() ? 'text-red-500' : ($client->expires_at->diffInDays() < 30 ? 'text-amber-600' : 'text-gray-400') }}">
                        {{ $client->expires_at->format('d/m/Y') }}
                    </span>
                    @else
                    <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    @php
                        $sc = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-500', 'revoked' => 'bg-red-100 text-red-700'];
                        $sl = ['active' => 'Activo', 'inactive' => 'Inactivo', 'revoked' => 'Revocado'];
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc[$client->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $sl[$client->status] ?? $client->status }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <a href="{{ route('admin.api-clients.show', $client) }}"
                       class="text-blue-600 hover:text-blue-800 text-xs font-medium whitespace-nowrap">Ver →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-5 py-12 text-center text-gray-400 text-sm">
                    No hay clientes API.
                    <a href="{{ route('admin.api-clients.create') }}" class="text-blue-600 hover:underline ml-1">Crear el primero</a>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t">{{ $clients->links() }}</div>
</div>
@endsection
