@extends('layouts.admin')
@section('title', $campaign->name)
@section('content')

@php
$statusColors = ['draft'=>'bg-yellow-50 text-yellow-700 border border-yellow-200','active'=>'bg-green-50 text-green-700 border border-green-200','paused'=>'bg-orange-50 text-orange-700 border border-orange-200','finished'=>'bg-gray-100 text-gray-600 border border-gray-200','cancelled'=>'bg-red-50 text-red-700 border border-red-200'];
$statusLabels = ['draft'=>'Borrador','active'=>'Activa','paused'=>'Pausada','finished'=>'Finalizada','cancelled'=>'Cancelada'];
$statusDots   = ['draft'=>'bg-yellow-400','active'=>'bg-green-500','paused'=>'bg-orange-400','finished'=>'bg-gray-400','cancelled'=>'bg-red-400'];
$typeLabels   = ['general'=>'General','sms'=>'SMS','product'=>'Producto','activation'=>'Activación'];
$typeColors   = ['general'=>'bg-blue-50 text-blue-700','sms'=>'bg-purple-50 text-purple-700','product'=>'bg-teal-50 text-teal-700','activation'=>'bg-orange-50 text-orange-700'];
$batchStatusColors = ['draft'=>'text-yellow-600','active'=>'text-green-600','paused'=>'text-orange-600','expired'=>'text-gray-500','cancelled'=>'text-red-500'];
$batchStatusLabels = ['draft'=>'Borrador','active'=>'Activo','paused'=>'Pausado','expired'=>'Expirado','cancelled'=>'Cancelado'];
@endphp

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.campaigns.index') }}" class="hover:text-gray-600 transition-colors">Campañas</a>
    <span>/</span>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $campaign->name }}</span>
</div>

{{-- Campaign header --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-5" x-data>
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$campaign->status] ?? 'bg-gray-400' }}"></span>
                    {{ $statusLabels[$campaign->status] ?? $campaign->status }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$campaign->type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $typeLabels[$campaign->type] ?? $campaign->type }}
                </span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $campaign->name }}</h1>
            @if($campaign->description)
                <p class="text-sm text-gray-500">{{ $campaign->description }}</p>
            @endif
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-wrap gap-2 flex-shrink-0">
            @if($campaign->status === 'active')
                <form method="POST" action="{{ route('admin.campaigns.pause', $campaign) }}">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 transition-colors">
                        ⏸ Pausar
                    </button>
                </form>
            @elseif(in_array($campaign->status, ['draft', 'paused']))
                <form method="POST" action="{{ route('admin.campaigns.activate', $campaign) }}">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition-colors">
                        ▶ Activar
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.campaigns.edit', $campaign) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 transition-colors">
                ✏ Editar
            </a>
            <form method="POST" action="{{ route('admin.campaigns.duplicate', $campaign) }}">
                @csrf
                <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white text-blue-600 hover:bg-blue-50 border border-blue-200 transition-colors">
                    ⎘ Duplicar
                </button>
            </form>
        </div>
    </div>

    {{-- Quick meta row --}}
    <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-500">
        @if($campaign->start_date || $campaign->end_date)
        <span>
            📅 {{ $campaign->start_date?->format('d/m/Y') ?? '—' }}
            → {{ $campaign->end_date?->format('d/m/Y') ?? 'sin límite' }}
        </span>
        @endif
        @if($campaign->budget)
        <span>💰 Presupuesto: <strong class="text-gray-700">$ {{ number_format($campaign->budget, 0, ',', '.') }} COP</strong></span>
        @endif
        <span>👤 Creada por <strong class="text-gray-700">{{ $campaign->createdBy?->name ?? 'Sistema' }}</strong></span>
        <span>🕐 {{ $campaign->created_at->diffForHumans() }}</span>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCoupons) }}</p>
        <p class="text-xs text-gray-400 mt-1">Cupones totales</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ number_format($totalRedemptions) }}</p>
        <p class="text-xs text-gray-400 mt-1">Redenciones</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold {{ $redemptionRate >= 50 ? 'text-green-600' : ($redemptionRate >= 20 ? 'text-yellow-600' : 'text-gray-600') }}">
            {{ $redemptionRate }}%
        </p>
        <p class="text-xs text-gray-400 mt-1">Tasa de redención</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-purple-600">$ {{ number_format($totalDiscount, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">Descuento emitido</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        @if($budgetUsed !== null)
            <p class="text-2xl font-bold {{ $budgetUsed > 80 ? 'text-red-600' : ($budgetUsed > 50 ? 'text-orange-500' : 'text-green-600') }}">
                {{ $budgetUsed }}%
            </p>
            <p class="text-xs text-gray-400 mt-1">Presupuesto usado</p>
        @else
            <p class="text-2xl font-bold text-gray-400">—</p>
            <p class="text-xs text-gray-400 mt-1">Sin presupuesto</p>
        @endif
    </div>
</div>

{{-- Budget progress bar --}}
@if($budgetUsed !== null)
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <div class="flex justify-between text-xs text-gray-500 mb-1.5">
        <span>Presupuesto utilizado</span>
        <span class="font-medium">$ {{ number_format($totalDiscount, 0, ',', '.') }} / $ {{ number_format($campaign->budget, 0, ',', '.') }} COP</span>
    </div>
    <div class="w-full bg-gray-100 rounded-full h-2">
        <div class="h-2 rounded-full transition-all {{ $budgetUsed > 80 ? 'bg-red-500' : ($budgetUsed > 50 ? 'bg-orange-400' : 'bg-green-500') }}"
             style="width: {{ min($budgetUsed, 100) }}%"></div>
    </div>
</div>
@endif

{{-- Tabs --}}
<div x-data="{ tab: '{{ request('tab', 'overview') }}' }">

    {{-- Tab nav --}}
    <div class="bg-white rounded-xl shadow-sm mb-5 overflow-hidden">
        <div class="flex border-b border-gray-100">
            @php
            $totalCampaignCustomers = $campaign->customers()->count();
            $tabs = [
                ['key'=>'overview',     'label'=>'Resumen',    'icon'=>'📋'],
                ['key'=>'batches',      'label'=>'Lotes (' . $campaign->couponBatches->count() . ')', 'icon'=>'🎫'],
                ['key'=>'customers',    'label'=>'Clientes (' . $totalCampaignCustomers . ')', 'icon'=>'👥'],
                ['key'=>'redemptions',  'label'=>'Redenciones (' . $totalRedemptions . ')', 'icon'=>'✅'],
                ['key'=>'geography',    'label'=>'Geografía (' . ($campaign->zones->count() + $campaign->pointsOfSale->count()) . ')', 'icon'=>'📍'],
                ['key'=>'activity',     'label'=>'Actividad',  'icon'=>'🕐'],
            ];
            @endphp
            @foreach($tabs as $t)
            <button @click="tab = '{{ $t['key'] }}'"
                    class="flex items-center gap-1.5 px-5 py-3.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                    :class="tab === '{{ $t['key'] }}' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                {{ $t['icon'] }} {{ $t['label'] }}
            </button>
            @endforeach
        </div>

        {{-- Tab: Resumen --}}
        <div x-show="tab === 'overview'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Detalles de la campaña</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Tipo</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$campaign->type] ?? '' }}">
                                    {{ $typeLabels[$campaign->type] ?? $campaign->type }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Estado</dt>
                            <dd>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$campaign->status] ?? '' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDots[$campaign->status] ?? '' }}"></span>
                                    {{ $statusLabels[$campaign->status] ?? $campaign->status }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Inicio</dt>
                            <dd class="font-medium text-gray-800">{{ $campaign->start_date?->format('d M Y') ?? 'No definido' }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Fin</dt>
                            <dd class="font-medium text-gray-800">{{ $campaign->end_date?->format('d M Y') ?? 'Sin límite' }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Presupuesto</dt>
                            <dd class="font-medium text-gray-800">
                                @if($campaign->budget)
                                    $ {{ number_format($campaign->budget, 0, ',', '.') }} COP
                                @else
                                    Sin límite
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between py-2">
                            <dt class="text-gray-500">Lotes de cupones</dt>
                            <dd class="font-medium text-gray-800">{{ $campaign->couponBatches->count() }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Información del sistema</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Creada por</dt>
                            <dd class="font-medium text-gray-800">{{ $campaign->createdBy?->name ?? 'Sistema' }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Fecha de creación</dt>
                            <dd class="font-medium text-gray-800">{{ $campaign->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Última modificación</dt>
                            <dd class="font-medium text-gray-800">{{ $campaign->updated_at->format('d M Y, H:i') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <dt class="text-gray-500">Campañas SMS vinculadas</dt>
                            <dd class="font-medium text-gray-800">{{ $campaign->smsCampaigns()->count() }}</dd>
                        </div>
                        <div class="flex justify-between py-2">
                            <dt class="text-gray-500">ID</dt>
                            <dd class="font-mono text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded">#{{ $campaign->id }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Tab: Lotes de Cupones --}}
        <div x-show="tab === 'batches'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Lotes de cupones</h3>
                <a href="{{ route('admin.coupon-batches.create') }}?campaign_id={{ $campaign->id }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                    + Agregar lote
                </a>
            </div>

            @if($batchStats->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-3">🎫</div>
                    <p class="text-sm">Esta campaña no tiene lotes de cupones todavía.</p>
                    <a href="{{ route('admin.coupon-batches.create') }}?campaign_id={{ $campaign->id }}"
                       class="mt-3 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Crear primer lote
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($batchStats as $stat)
                    @php $batch = $stat['batch']; @endphp
                    <div class="border border-gray-100 rounded-xl p-4 hover:border-gray-200 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <a href="{{ route('admin.coupon-batches.show', $batch) }}"
                                       class="font-semibold text-gray-900 hover:text-blue-600 transition-colors text-sm">
                                        {{ $batch->name }}
                                    </a>
                                    <span class="text-xs {{ $batchStatusColors[$batch->status] ?? 'text-gray-500' }} font-medium">
                                        {{ $batchStatusLabels[$batch->status] ?? $batch->status }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-3 text-xs text-gray-500 mt-1">
                                    <span class="{{ $batch->discount_type === 'percentage' ? 'text-blue-600' : 'text-teal-600' }} font-semibold">
                                        @if($batch->discount_type === 'percentage')
                                            {{ $batch->discount_value }}% off
                                        @else
                                            $ {{ number_format($batch->discount_value, 0, ',', '.') }} off
                                        @endif
                                    </span>
                                    <span>Tipo: {{ $batch->code_type === 'unique' ? 'Códigos únicos' : 'Código general' }}</span>
                                    <span>{{ $batch->start_date->format('d/m/Y') }} – {{ $batch->end_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                            <div class="flex gap-4 text-center flex-shrink-0">
                                <div>
                                    <p class="text-lg font-bold text-gray-800">{{ number_format($stat['total']) }}</p>
                                    <p class="text-xs text-gray-400">Cupones</p>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-blue-600">{{ number_format($stat['redeemed']) }}</p>
                                    <p class="text-xs text-gray-400">Redimidos</p>
                                </div>
                                <div>
                                    <p class="text-lg font-bold {{ $stat['rate'] >= 50 ? 'text-green-600' : 'text-gray-600' }}">
                                        {{ $stat['rate'] }}%
                                    </p>
                                    <p class="text-xs text-gray-400">Tasa</p>
                                </div>
                            </div>
                        </div>

                        {{-- Redemption rate mini bar --}}
                        @if($stat['total'] > 0)
                        <div class="mt-3">
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-blue-500 transition-all"
                                     style="width: {{ min($stat['rate'], 100) }}%"></div>
                            </div>
                        </div>
                        @endif

                        {{-- Batch actions --}}
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.coupon-batches.show', $batch) }}"
                               class="text-xs text-gray-500 hover:text-gray-700 px-3 py-1 rounded-md hover:bg-gray-100 transition-colors">
                                Ver detalle
                            </a>
                            @if($batch->status === 'active')
                                <form method="POST" action="{{ route('admin.coupon-batches.pause', $batch) }}">
                                    @csrf
                                    <button class="text-xs text-orange-600 hover:text-orange-800 px-3 py-1 rounded-md hover:bg-orange-50 transition-colors">Pausar</button>
                                </form>
                            @elseif(in_array($batch->status, ['draft','paused']))
                                <form method="POST" action="{{ route('admin.coupon-batches.activate', $batch) }}">
                                    @csrf
                                    <button class="text-xs text-green-600 hover:text-green-800 px-3 py-1 rounded-md hover:bg-green-50 transition-colors">Activar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab: Clientes --}}
        <div x-show="tab === 'customers'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">
                    Clientes vinculados
                    <span class="ml-1 text-xs font-normal text-gray-400">(base para campañas SMS)</span>
                </h3>
                <a href="{{ route('admin.campaigns.assign', $campaign) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                    + Asignar clientes
                </a>
            </div>

            @if($totalCampaignCustomers === 0)
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-3">👥</div>
                    <p class="text-sm">No hay clientes vinculados a esta campaña todavía.</p>
                    <a href="{{ route('admin.campaigns.assign', $campaign) }}"
                       class="mt-3 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Asignar primera base de clientes
                    </a>
                </div>
            @else
                @php
                    $previewCustomers = $campaign->customers()->with('city')->latest('campaign_customers.created_at')->limit(8)->get();
                @endphp
                <div class="space-y-2 mb-4">
                    @foreach($previewCustomers as $c)
                    <div class="flex items-center justify-between px-3 py-2.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                {{ strtoupper(substr($c->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $c->name }} {{ $c->lastname }}</p>
                                <p class="text-xs text-gray-400">{{ $c->phone }} @if($c->city) · {{ $c->city->name }} @endif</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.customers.show', $c) }}"
                           class="text-xs text-blue-600 hover:text-blue-800">Ver →</a>
                    </div>
                    @endforeach
                </div>
                @if($totalCampaignCustomers > 8)
                <a href="{{ route('admin.campaigns.customers', $campaign) }}"
                   class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium py-2 border border-blue-100 rounded-lg hover:bg-blue-50 transition-colors">
                    Ver los {{ number_format($totalCampaignCustomers) }} clientes →
                </a>
                @else
                <a href="{{ route('admin.campaigns.customers', $campaign) }}"
                   class="block text-center text-xs text-gray-500 hover:text-gray-700 mt-2">
                    Ver listado completo →
                </a>
                @endif
            @endif
        </div>

        {{-- Tab: Redenciones --}}
        <div x-show="tab === 'redemptions'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Últimas redenciones</h3>
                <a href="{{ route('admin.redemptions.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Ver todas →
                </a>
            </div>

            @if($recentRedemptions->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-3">✅</div>
                    <p class="text-sm">Aún no hay redenciones en esta campaña.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
                                <th class="text-left py-2 pr-4">Cupón</th>
                                <th class="text-left py-2 pr-4">Cliente</th>
                                <th class="text-left py-2 pr-4">Canal</th>
                                <th class="text-right py-2 pr-4">Descuento</th>
                                <th class="text-right py-2">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentRedemptions as $r)
                            <tr class="hover:bg-gray-50/60">
                                <td class="py-2.5 pr-4">
                                    <a href="{{ route('admin.redemptions.show', $r) }}"
                                       class="font-mono text-xs text-blue-600 hover:text-blue-800">
                                        {{ $r->coupon->code }}
                                    </a>
                                    @if($r->reversed_at)
                                        <span class="ml-1 text-xs text-red-500">(revertida)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-4 text-gray-600 text-xs">
                                    {{ $r->customer?->name ?? $r->customer?->phone ?? 'Anónimo' }}
                                </td>
                                <td class="py-2.5 pr-4">
                                    @php $channelColors = ['api'=>'bg-blue-50 text-blue-600','web'=>'bg-green-50 text-green-600','manual'=>'bg-gray-100 text-gray-600','sms'=>'bg-purple-50 text-purple-600']; @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $channelColors[$r->channel] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ strtoupper($r->channel) }}
                                    </span>
                                </td>
                                <td class="py-2.5 pr-4 text-right font-semibold text-gray-800 text-xs">
                                    - $ {{ number_format($r->discount_applied, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 text-right text-xs text-gray-400">
                                    {{ $r->redeemed_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($totalRedemptions > 10)
                    <p class="mt-3 text-xs text-gray-400 text-center">
                        Mostrando las 10 más recientes de {{ $totalRedemptions }} redenciones.
                        <a href="{{ route('admin.redemptions.index') }}" class="text-blue-600 hover:text-blue-800 ml-1">Ver todas</a>
                    </p>
                @endif
            @endif
        </div>

        {{-- Tab: Geografía --}}
        <div x-show="tab === 'geography'" class="p-6">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-gray-700">Cobertura geográfica</h3>
                <a href="{{ route('admin.campaigns.edit', $campaign) }}"
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">Editar cobertura →</a>
            </div>

            @if($campaign->zones->isEmpty() && $campaign->pointsOfSale->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-3">📍</div>
                    <p class="text-sm font-medium">Sin cobertura geográfica definida</p>
                    <p class="text-xs mt-1">Edita la campaña para asociar zonas o puntos de venta.</p>
                    <a href="{{ route('admin.campaigns.edit', $campaign) }}"
                       class="mt-3 inline-block text-xs text-blue-600 hover:underline">
                        Configurar cobertura →
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

                    {{-- Zonas asignadas --}}
                    @if($campaign->zones->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                            Zonas ({{ $campaign->zones->count() }})
                        </h4>
                        <div class="space-y-2">
                            @foreach($campaign->zones as $zone)
                            <div class="flex items-center justify-between bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                                <div>
                                    <p class="text-sm font-medium text-blue-800">{{ $zone->name }}</p>
                                    <p class="text-xs text-blue-500">{{ $zone->city->name }}</p>
                                </div>
                                <span class="text-xs {{ $zone->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $zone->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- PDVs asignados --}}
                    @if($campaign->pointsOfSale->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                            Puntos de venta ({{ $campaign->pointsOfSale->count() }})
                        </h4>
                        <div class="space-y-2">
                            @foreach($campaign->pointsOfSale as $pos)
                            <div class="flex items-center justify-between bg-green-50 border border-green-100 rounded-lg px-3 py-2">
                                <div>
                                    <p class="text-sm font-medium text-green-800">{{ $pos->name }}</p>
                                    <p class="text-xs text-green-500">
                                        {{ $pos->city->name }}
                                        @if($pos->zone) · {{ $pos->zone->name }}@endif
                                        <span class="font-mono ml-1">{{ $pos->code }}</span>
                                    </p>
                                </div>
                                <span class="text-xs {{ $pos->status === 'active' ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $pos->status === 'active' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            {{-- Analytics: Interacciones por ciudad --}}
            @if($geoStats->isNotEmpty())
            <div>
                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                    Redenciones por ciudad (clientes)
                </h4>
                @php $maxGeo = $geoStats->max('total'); @endphp
                <div class="space-y-2">
                    @foreach($geoStats as $geo)
                    @php $pct = $maxGeo > 0 ? round(($geo->total / $maxGeo) * 100) : 0; @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-600 w-28 truncate shrink-0">
                            {{ $cities->get($geo->city_id, 'Sin ciudad') }}
                        </span>
                        <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                            <div class="h-5 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full flex items-center px-2 transition-all"
                                 style="width: {{ max($pct, 5) }}%">
                                <span class="text-[10px] text-white font-bold">{{ $geo->total }}</span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 w-20 text-right shrink-0">
                            $ {{ number_format($geo->discount, 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-2">Basado en la ciudad registrada del cliente al momento de redimir.</p>
            </div>
            @elseif($totalRedemptions === 0)
            <div class="mt-4 bg-gray-50 border border-gray-100 rounded-xl p-5 text-center">
                <p class="text-sm text-gray-400">Aún no hay redenciones para mostrar estadísticas geográficas.</p>
            </div>
            @endif

        </div>

        {{-- Tab: Actividad --}}
        <div x-show="tab === 'activity'" class="p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Historial de actividad</h3>

            @if($activityLog->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-3">🕐</div>
                    <p class="text-sm">No hay registros de actividad para esta campaña.</p>
                </div>
            @else
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-px bg-gray-200"></div>
                    <div class="space-y-4">
                        @foreach($activityLog as $log)
                        @php
                            $eventColors = ['created'=>'bg-green-500','updated'=>'bg-blue-500','activated'=>'bg-green-500','paused'=>'bg-orange-400','deleted'=>'bg-red-500','duplicated'=>'bg-purple-500'];
                            $eventLabels = ['created'=>'Creada','updated'=>'Actualizada','activated'=>'Activada','paused'=>'Pausada','deleted'=>'Eliminada','duplicated'=>'Duplicada'];
                        @endphp
                        <div class="relative flex gap-4 pl-9">
                            <span class="absolute left-2 top-1 w-4 h-4 rounded-full border-2 border-white {{ $eventColors[$log->event] ?? 'bg-gray-400' }} shadow-sm"></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-800">
                                        Campaña {{ $eventLabels[$log->event] ?? $log->event }}
                                    </p>
                                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($log->user_id)
                                    <p class="text-xs text-gray-500 mt-0.5">Por {{ $log->user?->name ?? 'Usuario #' . $log->user_id }}</p>
                                @endif
                                @if($log->event === 'updated' && $log->new_values)
                                    @php $changes = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true); @endphp
                                    @if($changes)
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach(array_keys($changes) as $field)
                                            @if(!in_array($field, ['updated_at']))
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $field }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
