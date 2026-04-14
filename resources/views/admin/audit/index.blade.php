@extends('layouts.admin')
@section('title', 'Auditoría')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Auditoría</h1>
        <p class="text-sm text-gray-500 mt-0.5">Registro completo de todas las acciones realizadas en la plataforma</p>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-5 text-sm">
    {{ session('success') }}
</div>
@endif

{{-- ── Alertas de seguridad ──────────────────────────────────────────────── --}}
@if($unresolvedAlerts->isNotEmpty())
<div class="mb-6 space-y-2">
    <div class="flex items-center gap-2 mb-2">
        <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
        </span>
        <p class="text-sm font-semibold text-red-700">{{ $unresolvedAlerts->count() }} alerta(s) de seguridad sin resolver</p>
    </div>
    @foreach($unresolvedAlerts as $alert)
    <div class="flex items-start gap-3 p-4 rounded-xl border
        @if($alert->severity === 'critical') bg-red-50 border-red-200
        @elseif($alert->severity === 'high') bg-orange-50 border-orange-200
        @elseif($alert->severity === 'medium') bg-amber-50 border-amber-200
        @else bg-yellow-50 border-yellow-200 @endif">

        <div class="flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4 {{ $alert->severity === 'critical' ? 'text-red-500' : ($alert->severity === 'high' ? 'text-orange-500' : 'text-amber-500') }}"
                 fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-0.5">
                <span class="text-xs font-bold uppercase tracking-wide
                    @if($alert->severity === 'critical') text-red-700
                    @elseif($alert->severity === 'high') text-orange-700
                    @else text-amber-700 @endif">{{ $alert->severity }}</span>
                <span class="text-xs text-gray-500 font-mono">{{ $alert->type }}</span>
                <span class="text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</span>
                @if($alert->ip_address)
                <span class="text-xs font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ $alert->ip_address }}</span>
                @endif
            </div>
            <p class="text-sm text-gray-700">{{ $alert->description }}</p>
        </div>
        <form method="POST" action="{{ route('admin.audit.alerts.resolve', $alert) }}" class="flex-shrink-0">
            @csrf
            <button type="submit"
                    class="text-xs text-gray-600 hover:text-gray-800 bg-white border border-gray-200 hover:border-gray-300 px-2.5 py-1.5 rounded-lg transition-colors">
                Resolver
            </button>
        </form>
    </div>
    @endforeach
</div>
@endif

{{-- ── Métricas ──────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total registros</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Hoy</p>
        <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['today']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Esta semana</p>
        <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['week']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Usuarios activos</p>
        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['users']) }}</p>
    </div>
</div>

{{-- ── Filtros ───────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('admin.audit.index') }}">
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-3">

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar (IP / URL)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="192.168.1.1"
                       class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Acción</label>
                <select name="event" class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    <option value="">Todas</option>
                    @foreach($events as $ev)
                    <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$ev)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Usuario</label>
                <select name="user_id" class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    <option value="">Todos</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Módulo</label>
                <select name="entity" class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    <option value="">Todos</option>
                    @foreach([
                        'Campaign'         => 'Campañas',
                        'CouponBatch'      => 'Lotes de Cupones',
                        'SmsCampaign'      => 'Campañas SMS',
                        'Customer'         => 'Clientes',
                        'User'             => 'Usuarios',
                        'LegalDocument'    => 'Documentos Legales',
                        'ApiClient'        => 'API Clients',
                        'LandingPageConfig'=> 'Landing Pages',
                    ] as $class => $label)
                    <option value="{{ $class }}" {{ request('entity') === $class ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                Filtrar
            </button>
            <a href="{{ route('admin.audit.index') }}"
               class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg transition-colors">
                Limpiar
            </a>
            @if(request()->hasAny(['search','event','user_id','entity','date_from','date_to']))
            <span class="text-xs text-blue-600 font-medium">
                {{ number_format($logs->total()) }} resultado(s)
            </span>
            @endif
        </div>
    </form>
</div>

{{-- ── Tabla de logs ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($logs->isEmpty())
    <div class="text-center py-16">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm text-gray-500">No hay registros para los filtros aplicados.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/60">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acción</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Módulo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($logs as $log)
                @php
                    $eventColors = [
                        'created'            => 'bg-green-100 text-green-700',
                        'updated'            => 'bg-blue-100 text-blue-700',
                        'deleted'            => 'bg-red-100 text-red-700',
                        'activated'          => 'bg-emerald-100 text-emerald-700',
                        'deactivated'        => 'bg-orange-100 text-orange-700',
                        'paused'             => 'bg-amber-100 text-amber-700',
                        'published'          => 'bg-teal-100 text-teal-700',
                        'revoked'            => 'bg-red-100 text-red-700',
                        'rotated_secret'     => 'bg-purple-100 text-purple-700',
                        'sent'               => 'bg-cyan-100 text-cyan-700',
                        'customers_assigned' => 'bg-indigo-100 text-indigo-700',
                        'customers_imported' => 'bg-violet-100 text-violet-700',
                        'duplicated'         => 'bg-gray-100 text-gray-600',
                        'cancelled'          => 'bg-rose-100 text-rose-700',
                        'retried'            => 'bg-yellow-100 text-yellow-700',
                    ];
                    $colorClass = $eventColors[$log->event] ?? 'bg-gray-100 text-gray-600';

                    $entityShort = $log->auditable_type ? class_basename($log->auditable_type) : null;
                    $entityLabels = [
                        'Campaign'         => 'Campaña',
                        'CouponBatch'      => 'Lote',
                        'SmsCampaign'      => 'SMS',
                        'Customer'         => 'Cliente',
                        'User'             => 'Usuario',
                        'LegalDocument'    => 'Doc. Legal',
                        'ApiClient'        => 'API Client',
                        'LandingPageConfig'=> 'Landing',
                    ];
                @endphp
                <tr class="hover:bg-gray-50/80 transition-colors">

                    <td class="px-4 py-3 whitespace-nowrap">
                        <p class="text-xs font-medium text-gray-800">{{ $log->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $log->created_at->format('H:i:s') }}</p>
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $colorClass }}">
                            {{ ucfirst(str_replace('_', ' ', $log->event)) }}
                        </span>
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($entityShort)
                        <span class="text-xs font-medium text-gray-700">{{ $entityLabels[$entityShort] ?? $entityShort }}</span>
                        @if($log->auditable_id)
                            <span class="text-xs text-gray-400 ml-1">#{{ $log->auditable_id }}</span>
                        @endif
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        @if($log->user)
                        <p class="text-xs font-medium text-gray-800">{{ $log->user->name }}</p>
                        @else
                        <span class="text-xs text-gray-400 italic">Sistema</span>
                        @endif
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-xs font-mono text-gray-500">{{ $log->ip_address ?? '—' }}</span>
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <a href="{{ route('admin.audit.show', $log->id) }}"
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            Ver →
                        </a>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-100 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            @if($logs->total() > 0)
                Mostrando {{ $logs->firstItem() }}–{{ $logs->lastItem() }} de {{ number_format($logs->total()) }} registros
            @else
                Sin registros
            @endif
        </p>
        @if($logs->hasPages())
        <div class="text-xs">{{ $logs->links() }}</div>
        @endif
    </div>
    @endif
</div>

@endsection
