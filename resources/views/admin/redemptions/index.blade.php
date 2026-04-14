@extends('layouts.admin')
@section('title', 'Redenciones')
@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Redenciones</h1>
        <p class="text-sm text-gray-500 mt-0.5">Log completo de cupones redimidos</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Redenciones</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
        @if(request()->hasAny(['date_from','date_to','channel']))
            <p class="text-xs text-gray-400 mt-0.5">con filtros aplicados</p>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Descuento emitido</p>
        <p class="text-xl font-bold text-green-600 mt-1">$ {{ number_format($stats['total_discount'], 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">COP</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Ingreso neto</p>
        <p class="text-xl font-bold text-purple-600 mt-1">$ {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-0.5">COP</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-400">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Reversadas</p>
        <p class="text-2xl font-bold text-red-500 mt-1">{{ number_format($stats['reversed']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Hoy</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($stats['today']) }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Código de cupón</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar código..."
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Canal</label>
            <select name="channel" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los canales</option>
                @foreach(['api'=>'API','web'=>'Web','manual'=>'Manual','sms'=>'SMS'] as $val => $label)
                <option value="{{ $val }}" {{ request('channel') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Activas</option>
                <option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>Reversadas</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Filtrar
            </button>
            @if(request()->hasAny(['search','date_from','date_to','channel','status','campaign_id']))
                <a href="{{ route('admin.redemptions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($redemptions->isEmpty())
        <div class="text-center py-16">
            <div class="text-5xl mb-4">✅</div>
            <p class="text-gray-500 font-medium">No hay redenciones
                @if(request()->hasAny(['search','date_from','date_to','channel','status'])) que coincidan con los filtros @endif
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="text-left px-4 py-3">#</th>
                        <th class="text-left px-4 py-3">Cupón / Lote</th>
                        <th class="text-left px-4 py-3">Cliente</th>
                        <th class="text-right px-4 py-3">Original</th>
                        <th class="text-right px-4 py-3">Descuento</th>
                        <th class="text-right px-4 py-3">Final</th>
                        <th class="text-center px-4 py-3">Canal</th>
                        <th class="text-left px-4 py-3">Fecha</th>
                        <th class="text-right px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($redemptions as $r)
                    @php
                        $channelColors = [
                            'api'    => 'bg-blue-50 text-blue-700',
                            'web'    => 'bg-green-50 text-green-700',
                            'manual' => 'bg-gray-100 text-gray-600',
                            'sms'    => 'bg-purple-50 text-purple-700',
                        ];
                        $isReversed = (bool) $r->reversed_at;
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors {{ $isReversed ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3 text-gray-400 text-xs font-mono">#{{ $r->id }}</td>

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.redemptions.show', $r) }}"
                                   class="font-mono text-xs font-semibold bg-gray-100 hover:bg-blue-50 hover:text-blue-700 px-2 py-0.5 rounded transition-colors">
                                    {{ $r->coupon->code }}
                                </a>
                                @if($isReversed)
                                    <span class="text-xs bg-red-50 text-red-600 border border-red-200 px-1.5 py-0.5 rounded font-medium">REVERTIDA</span>
                                @endif
                            </div>
                            @if($r->coupon->batch ?? false)
                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-48">
                                    {{ $r->coupon->batch->name }}
                                    @if($r->coupon->batch->campaign ?? false)
                                        <span class="text-gray-300 mx-1">·</span>{{ $r->coupon->batch->campaign->name }}
                                    @endif
                                </p>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if($r->customer)
                                <p class="font-medium text-gray-800 text-xs">{{ $r->customer->name }} {{ $r->customer->lastname }}</p>
                                <p class="text-xs text-gray-400">{{ $r->customer->phone }}</p>
                            @else
                                <span class="text-xs text-gray-400">Anónimo</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right text-gray-600 text-xs">
                            $ {{ number_format($r->original_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm font-semibold {{ $isReversed ? 'text-gray-400 line-through' : 'text-green-600' }}">
                                - $ {{ number_format($r->discount_applied, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            $ {{ number_format($r->final_amount, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $channelColors[$r->channel] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ strtoupper($r->channel) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-xs text-gray-500">
                            <div>{{ $r->redeemed_at->format('d/m/Y') }}</div>
                            <div class="text-gray-400">{{ $r->redeemed_at->format('H:i') }}</div>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.redemptions.show', $r) }}"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    Ver
                                </a>
                                @if(!$isReversed)
                                    <span class="text-gray-200">|</span>
                                    <form method="POST" action="{{ route('admin.redemptions.reverse', $r) }}"
                                          onsubmit="return confirm('¿Reversar la redención #{{ $r->id }}? Se restaurará el cupón {{ $r->coupon->code }}.')">
                                        @csrf
                                        <button class="text-xs text-red-500 hover:text-red-700 font-medium">Reversar</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Reversada</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Mostrando {{ $redemptions->firstItem() }}–{{ $redemptions->lastItem() }}
                de {{ number_format($redemptions->total()) }} redenciones
            </p>
            {{ $redemptions->links() }}
        </div>
    @endif
</div>

@endsection
