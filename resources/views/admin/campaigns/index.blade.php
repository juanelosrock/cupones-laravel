@extends('layouts.admin')
@section('title', 'Campañas')
@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Campañas</h1>
        <p class="text-sm text-gray-500 mt-0.5">Gestiona y monitorea todas tus campañas de descuento</p>
    </div>
    <a href="{{ route('admin.campaigns.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
        + Nueva Campaña
    </a>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-300">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Activas</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Borrador</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['draft'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-400">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Pausadas</p>
        <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['paused'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-400">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Finalizadas</p>
        <p class="text-2xl font-bold text-gray-600 mt-1">{{ $stats['finished'] }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nombre de campaña..."
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Borrador</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Activa</option>
                <option value="paused"    {{ request('status') === 'paused'    ? 'selected' : '' }}>Pausada</option>
                <option value="finished"  {{ request('status') === 'finished'  ? 'selected' : '' }}>Finalizada</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
            <select name="type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="general"    {{ request('type') === 'general'    ? 'selected' : '' }}>General</option>
                <option value="sms"        {{ request('type') === 'sms'        ? 'selected' : '' }}>SMS</option>
                <option value="product"    {{ request('type') === 'product'    ? 'selected' : '' }}>Producto</option>
                <option value="activation" {{ request('type') === 'activation' ? 'selected' : '' }}>Activación</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Filtrar
            </button>
            @if(request()->hasAny(['search','status','type']))
                <a href="{{ route('admin.campaigns.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($campaigns->isEmpty())
        <div class="text-center py-16">
            <div class="text-5xl mb-4">📣</div>
            <p class="text-gray-500 font-medium">No hay campañas
                @if(request()->hasAny(['search','status','type'])) que coincidan con los filtros @endif
            </p>
            <a href="{{ route('admin.campaigns.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                + Crear primera campaña
            </a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Campaña</th>
                    <th class="text-left px-4 py-3">Tipo</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    <th class="text-left px-4 py-3">Vigencia</th>
                    <th class="text-center px-4 py-3">Lotes</th>
                    <th class="text-right px-4 py-3">Presupuesto</th>
                    <th class="text-right px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($campaigns as $campaign)
                @php
                    $typeColors = ['general'=>'bg-blue-50 text-blue-700','sms'=>'bg-purple-50 text-purple-700','product'=>'bg-teal-50 text-teal-700','activation'=>'bg-orange-50 text-orange-700'];
                    $typeLabels = ['general'=>'General','sms'=>'SMS','product'=>'Producto','activation'=>'Activación'];
                    $statusColors = ['draft'=>'bg-yellow-50 text-yellow-700 border border-yellow-200','active'=>'bg-green-50 text-green-700 border border-green-200','paused'=>'bg-orange-50 text-orange-700 border border-orange-200','finished'=>'bg-gray-100 text-gray-600 border border-gray-200','cancelled'=>'bg-red-50 text-red-700 border border-red-200'];
                    $statusLabels = ['draft'=>'Borrador','active'=>'Activa','paused'=>'Pausada','finished'=>'Finalizada','cancelled'=>'Cancelada'];
                    $statusDots = ['draft'=>'bg-yellow-400','active'=>'bg-green-500','paused'=>'bg-orange-400','finished'=>'bg-gray-400','cancelled'=>'bg-red-400'];
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors" x-data="{ open: false }">
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.campaigns.show', $campaign) }}" class="font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                            {{ $campaign->name }}
                        </a>
                        @if($campaign->description)
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ Str::limit($campaign->description, 60) }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$campaign->type] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $typeLabels[$campaign->type] ?? $campaign->type }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$campaign->status] ?? 'bg-gray-400' }}"></span>
                            {{ $statusLabels[$campaign->status] ?? $campaign->status }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-gray-500 text-xs">
                        @if($campaign->start_date || $campaign->end_date)
                            <div>{{ $campaign->start_date?->format('d/m/Y') ?? '—' }}</div>
                            <div class="text-gray-400">hasta {{ $campaign->end_date?->format('d/m/Y') ?? 'sin límite' }}</div>
                        @else
                            <span class="text-gray-400">Sin fechas</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                            {{ $campaign->coupon_batches_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $campaign->coupon_batches_count }}
                        </span>
                        @if(($campaign->active_batches_count ?? 0) > 0)
                            <div class="text-xs text-green-600 mt-0.5">{{ $campaign->active_batches_count }} activo(s)</div>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-right text-gray-700">
                        @if($campaign->budget)
                            <span class="font-medium">$ {{ number_format($campaign->budget, 0, ',', '.') }}</span>
                            <span class="text-xs text-gray-400 block">COP</span>
                        @else
                            <span class="text-gray-400 text-xs">Sin límite</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="relative inline-block">
                            <button @click="open = !open" @click.outside="open = false"
                                    class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-lg leading-none">
                                ⋮
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-lg border border-gray-100 z-20 py-1">
                                <a href="{{ route('admin.campaigns.show', $campaign) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Ver detalle</a>
                                <a href="{{ route('admin.campaigns.edit', $campaign) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Editar</a>
                                @if($campaign->status === 'active')
                                    <form method="POST" action="{{ route('admin.campaigns.pause', $campaign) }}">
                                        @csrf
                                        <button class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">Pausar</button>
                                    </form>
                                @elseif(in_array($campaign->status, ['draft','paused']))
                                    <form method="POST" action="{{ route('admin.campaigns.activate', $campaign) }}">
                                        @csrf
                                        <button class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50">Activar</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.campaigns.duplicate', $campaign) }}">
                                    @csrf
                                    <button class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">Duplicar</button>
                                </form>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}"
                                      onsubmit="return confirm('¿Eliminar campaña «{{ addslashes($campaign->name) }}»?')">
                                    @csrf @method('DELETE')
                                    <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($campaigns->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Mostrando {{ $campaigns->firstItem() }}–{{ $campaigns->lastItem() }} de {{ $campaigns->total() }} campañas
                </p>
                {{ $campaigns->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
