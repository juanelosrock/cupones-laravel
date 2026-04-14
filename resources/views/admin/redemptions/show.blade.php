@extends('layouts.admin')
@section('title', 'Redención #' . $redemption->id)
@section('content')

@php
    $isReversed = (bool) $redemption->reversed_at;
    $channelColors = [
        'api'    => 'bg-blue-50 text-blue-700 border border-blue-200',
        'web'    => 'bg-green-50 text-green-700 border border-green-200',
        'manual' => 'bg-gray-100 text-gray-600 border border-gray-200',
        'sms'    => 'bg-purple-50 text-purple-700 border border-purple-200',
    ];
    $batch    = $redemption->coupon->batch ?? null;
    $campaign = $batch->campaign ?? null;
@endphp

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.redemptions.index') }}" class="hover:text-gray-600 transition-colors">Redenciones</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">#{{ $redemption->id }}</span>
</div>

{{-- Header --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-5">
    <div class="flex flex-col md:flex-row md:items-start gap-4">
        <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                {{-- Status badge --}}
                @if($isReversed)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Reversada
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Válida
                    </span>
                @endif
                {{-- Channel badge --}}
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $channelColors[$redemption->channel] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ strtoupper($redemption->channel) }}
                </span>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 font-mono tracking-wide">
                {{ $redemption->coupon->code }}
            </h1>
            @if($batch)
                <p class="text-sm text-gray-500 mt-1">
                    Lote: <a href="{{ route('admin.coupon-batches.show', $batch) }}" class="text-blue-600 hover:underline">{{ $batch->name }}</a>
                    @if($campaign)
                        <span class="text-gray-300 mx-1.5">·</span>
                        Campaña: <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-blue-600 hover:underline">{{ $campaign->name }}</a>
                    @endif
                </p>
            @endif
        </div>

        {{-- Reverse action --}}
        @if(!$isReversed)
            <form method="POST" action="{{ route('admin.redemptions.reverse', $redemption) }}"
                  onsubmit="return confirm('¿Reversar esta redención? Se restaurará el cupón y se decrementará el contador de usos.')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">
                    ↩ Reversar redención
                </button>
            </form>
        @else
            <div class="text-xs text-gray-500 text-right bg-red-50 border border-red-100 rounded-lg px-4 py-2">
                <p class="font-medium text-red-700">Redención reversada</p>
                <p class="mt-0.5">{{ $redemption->reversed_at->format('d/m/Y H:i') }}</p>
                @if($redemption->reversedBy)
                    <p class="mt-0.5">por {{ $redemption->reversedBy->name }}</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Quick meta row --}}
    <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-500">
        <span>🕐 {{ $redemption->redeemed_at->format('d M Y, H:i:s') }}</span>
        @if($redemption->order_reference)
            <span>📋 Ref: <strong class="text-gray-700">{{ $redemption->order_reference }}</strong></span>
        @endif
        @if($redemption->user)
            <span>👤 Procesada por <strong class="text-gray-700">{{ $redemption->user->name }}</strong></span>
        @endif
        <span>🌐 IP: <strong class="text-gray-700 font-mono">{{ $redemption->ip_address ?? '—' }}</strong></span>
    </div>
</div>

{{-- Amounts breakdown --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-5">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Desglose de montos</h2>
    <div class="flex items-center gap-4 flex-wrap">
        <div class="flex-1 min-w-32 text-center bg-gray-50 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Monto original</p>
            <p class="text-2xl font-bold text-gray-700">$ {{ number_format($redemption->original_amount, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">COP</p>
        </div>
        <div class="text-2xl text-gray-300 font-light">−</div>
        <div class="flex-1 min-w-32 text-center bg-green-50 rounded-xl p-4 border border-green-100">
            <p class="text-xs text-green-600 mb-1">Descuento aplicado</p>
            <p class="text-2xl font-bold text-green-600">$ {{ number_format($redemption->discount_applied, 0, ',', '.') }}</p>
            @if($batch)
                <p class="text-xs text-green-500 mt-0.5">
                    @if($batch->discount_type === 'percentage')
                        {{ $batch->discount_value }}% off
                    @else
                        Fijo
                    @endif
                </p>
            @endif
        </div>
        <div class="text-2xl text-gray-300 font-light">=</div>
        <div class="flex-1 min-w-32 text-center bg-blue-50 rounded-xl p-4 border border-blue-100">
            <p class="text-xs text-blue-600 mb-1">Monto final pagado</p>
            <p class="text-2xl font-bold text-blue-700">$ {{ number_format($redemption->final_amount, 0, ',', '.') }}</p>
            <p class="text-xs text-blue-500 mt-0.5">COP</p>
        </div>
    </div>

    {{-- Discount bar --}}
    @php
        $pct = $redemption->original_amount > 0
            ? round(($redemption->discount_applied / $redemption->original_amount) * 100, 1)
            : 0;
    @endphp
    <div class="mt-4">
        <div class="flex justify-between text-xs text-gray-400 mb-1">
            <span>Ahorro del {{ $pct }}%</span>
            <span>sobre $ {{ number_format($redemption->original_amount, 0, ',', '.') }}</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full bg-green-400" style="width: {{ min($pct, 100) }}%"></div>
        </div>
    </div>
</div>

{{-- Two-column detail --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

    {{-- Customer --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Cliente</h2>
        @if($redemption->customer)
            @php $c = $redemption->customer; @endphp
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm flex-shrink-0">
                    {{ strtoupper(substr($c->name, 0, 1)) }}{{ strtoupper(substr($c->lastname ?? '', 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $c->name }} {{ $c->lastname }}</p>
                    <a href="{{ route('admin.customers.show', $c) }}" class="text-xs text-blue-600 hover:underline">Ver perfil completo →</a>
                </div>
            </div>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Documento</dt>
                    <dd class="font-medium text-gray-700">{{ $c->document_type }} {{ $c->document_number }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Teléfono</dt>
                    <dd class="font-medium text-gray-700">{{ $c->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Email</dt>
                    <dd class="font-medium text-gray-700 text-xs">{{ $c->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-gray-400">Ciudad</dt>
                    <dd class="font-medium text-gray-700">{{ $c->city?->name ?? '—' }}</dd>
                </div>
            </dl>
        @else
            <div class="text-center py-6 text-gray-400">
                <div class="text-3xl mb-2">👤</div>
                <p class="text-sm">Redención anónima</p>
                <p class="text-xs mt-1">No se asoció cliente a esta redención</p>
            </div>
        @endif
    </div>

    {{-- Coupon & Batch info --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Cupón y lote</h2>
        @if($batch)
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Código</dt>
                    <dd>
                        <span class="font-mono font-semibold bg-gray-100 px-2 py-0.5 rounded text-sm">
                            {{ $redemption->coupon->code }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Tipo de código</dt>
                    <dd class="font-medium text-gray-700">{{ $batch->code_type === 'unique' ? 'Único' : 'General' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Lote</dt>
                    <dd>
                        <a href="{{ route('admin.coupon-batches.show', $batch) }}"
                           class="font-medium text-blue-600 hover:underline">{{ $batch->name }}</a>
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Descuento del lote</dt>
                    <dd class="font-semibold text-gray-700">
                        @if($batch->discount_type === 'percentage')
                            {{ $batch->discount_value }}%
                        @else
                            $ {{ number_format($batch->discount_value, 0, ',', '.') }}
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-gray-400">Vigencia del lote</dt>
                    <dd class="font-medium text-gray-700 text-xs">
                        {{ $batch->start_date->format('d/m/Y') }} – {{ $batch->end_date->format('d/m/Y') }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-gray-400">Campaña</dt>
                    <dd>
                        @if($campaign)
                            <a href="{{ route('admin.campaigns.show', $campaign) }}"
                               class="font-medium text-blue-600 hover:underline">{{ $campaign->name }}</a>
                        @else
                            <span class="text-gray-400">Sin campaña</span>
                        @endif
                    </dd>
                </div>
            </dl>
        @else
            <div class="text-center py-6 text-gray-400 text-sm">
                Información del lote no disponible
            </div>
        @endif
    </div>
</div>

{{-- Technical & Metadata --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Technical details --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos técnicos</h2>
        <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <dt class="text-gray-400">ID redención</dt>
                <dd class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-700">#{{ $redemption->id }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <dt class="text-gray-400">Canal</dt>
                <dd>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $channelColors[$redemption->channel] ?? '' }}">
                        {{ strtoupper($redemption->channel) }}
                    </span>
                </dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <dt class="text-gray-400">Referencia de orden</dt>
                <dd class="font-medium text-gray-700 font-mono text-xs">{{ $redemption->order_reference ?? '—' }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <dt class="text-gray-400">IP</dt>
                <dd class="font-mono text-xs text-gray-700">{{ $redemption->ip_address ?? '—' }}</dd>
            </div>
            <div class="py-1.5 border-b border-gray-50">
                <dt class="text-gray-400 mb-1">User Agent</dt>
                <dd class="font-mono text-xs text-gray-600 break-all leading-relaxed">{{ $redemption->user_agent ?? '—' }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <dt class="text-gray-400">Procesada por</dt>
                <dd class="font-medium text-gray-700">{{ $redemption->user?->name ?? 'Sistema/API' }}</dd>
            </div>
            <div class="flex justify-between py-1.5">
                <dt class="text-gray-400">Fecha y hora</dt>
                <dd class="font-medium text-gray-700 text-xs">{{ $redemption->redeemed_at->format('d M Y, H:i:s') }}</dd>
            </div>
        </dl>
    </div>

    {{-- Metadata & Reversal --}}
    <div class="space-y-5">

        {{-- Reversal info --}}
        @if($isReversed)
        <div class="bg-red-50 border border-red-100 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-red-700 mb-3">Información de reversión</h2>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between py-1.5 border-b border-red-100">
                    <dt class="text-red-400">Reversada el</dt>
                    <dd class="font-medium text-red-700">{{ $redemption->reversed_at->format('d M Y, H:i:s') }}</dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-red-400">Reversada por</dt>
                    <dd class="font-medium text-red-700">{{ $redemption->reversedBy?->name ?? '—' }}</dd>
                </div>
            </dl>
            <p class="mt-3 text-xs text-red-500">
                Al reversar, el cupón <strong class="font-mono">{{ $redemption->coupon->code }}</strong> fue restaurado y el contador de usos decrementado.
            </p>
        </div>
        @endif

        {{-- Metadata --}}
        @php $meta = is_array($redemption->metadata) ? $redemption->metadata : (is_string($redemption->metadata) ? json_decode($redemption->metadata, true) : null); @endphp
        @if($meta && count($meta) > 0)
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Metadata</h2>
            <div class="bg-gray-50 rounded-lg p-3">
                <pre class="text-xs text-gray-600 overflow-x-auto whitespace-pre-wrap break-all font-mono">{{ json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif

        {{-- Navigation --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Acciones</h2>
            <div class="space-y-2">
                <a href="{{ route('admin.redemptions.index') }}"
                   class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50 border border-gray-200 transition-colors">
                    <span>← Volver al log</span>
                </a>
                @if($batch)
                <a href="{{ route('admin.coupon-batches.show', $batch) }}"
                   class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm text-blue-600 hover:bg-blue-50 border border-blue-100 transition-colors">
                    <span>Ver lote de cupones</span>
                    <span>→</span>
                </a>
                @endif
                @if($campaign)
                <a href="{{ route('admin.campaigns.show', $campaign) }}"
                   class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm text-blue-600 hover:bg-blue-50 border border-blue-100 transition-colors">
                    <span>Ver campaña</span>
                    <span>→</span>
                </a>
                @endif
                @if($redemption->customer)
                <a href="{{ route('admin.customers.show', $redemption->customer) }}"
                   class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm text-blue-600 hover:bg-blue-50 border border-blue-100 transition-colors">
                    <span>Ver perfil del cliente</span>
                    <span>→</span>
                </a>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
