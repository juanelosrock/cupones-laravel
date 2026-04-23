@extends('layouts.admin')
@section('title', $emailCampaign->name)
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.email-campaigns.index') }}" class="hover:text-gray-600">Campañas Email</a>
    <span>/</span>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $emailCampaign->name }}</span>
</div>

{{-- Header --}}
<div class="flex items-start justify-between mb-4">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ $emailCampaign->name }}</h1>
            @php
                $statusColors = [
                    'draft'     => 'bg-gray-100 text-gray-600',
                    'scheduled' => 'bg-yellow-100 text-yellow-700',
                    'sending'   => 'bg-blue-100 text-blue-700',
                    'sent'      => 'bg-green-100 text-green-700',
                    'failed'    => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-100 text-gray-500',
                ];
                $statusLabels = [
                    'draft'     => 'Borrador',
                    'scheduled' => 'Programada',
                    'sending'   => 'Enviando',
                    'sent'      => 'Enviada',
                    'failed'    => 'Fallida',
                    'cancelled' => 'Cancelada',
                ];
            @endphp
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$emailCampaign->status] ?? 'bg-gray-100 text-gray-600' }}">
                <span class="w-1.5 h-1.5 rounded-full
                    {{ $emailCampaign->status === 'sent'      ? 'bg-green-500' :
                       ($emailCampaign->status === 'sending'  ? 'bg-blue-500 animate-pulse' :
                       ($emailCampaign->status === 'failed'   ? 'bg-red-500' :
                       ($emailCampaign->status === 'scheduled'? 'bg-yellow-500' : 'bg-gray-400'))) }}">
                </span>
                {{ $statusLabels[$emailCampaign->status] ?? $emailCampaign->status }}
            </span>
        </div>
        <p class="text-sm text-gray-500">
            Creada {{ $emailCampaign->created_at->diffForHumans() }}
            @if($emailCampaign->createdBy) por <strong>{{ $emailCampaign->createdBy->name }}</strong>@endif
        </p>
    </div>

    <div class="flex items-center gap-2 flex-wrap justify-end">
        @if(in_array($emailCampaign->status, ['draft', 'scheduled', 'failed']))
            <form method="POST" action="{{ route('admin.email-campaigns.send', $emailCampaign) }}"
                  onsubmit="return confirm('¿Confirmas el envío de {{ number_format($emailCampaign->total_recipients) }} emails?')">
                @csrf
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Enviar ahora
                </button>
            </form>
        @endif

        @if($recipientStats['failed'] > 0)
            <form method="POST" action="{{ route('admin.email-campaigns.retry', $emailCampaign) }}"
                  onsubmit="return confirm('¿Reintentar los {{ number_format($recipientStats['failed']) }} envíos fallidos?')">
                @csrf
                <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Reintentar fallidos ({{ number_format($recipientStats['failed']) }})
                </button>
            </form>
        @endif

        @if($recipientStats['pending'] > 0 && !in_array($emailCampaign->status, ['draft', 'scheduled', 'cancelled']))
            <form method="POST" action="{{ route('admin.email-campaigns.process-pending', $emailCampaign) }}"
                  onsubmit="return confirm('¿Despachar {{ number_format($recipientStats['pending']) }} destinatario(s) pendiente(s) a la cola?')">
                @csrf
                <button class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Procesar {{ number_format($recipientStats['pending']) }} pendiente(s)
                </button>
            </form>
        @endif

        @if($missingCount > 0 && $emailCampaign->status !== 'cancelled')
            <form method="POST" action="{{ route('admin.email-campaigns.sync-recipients', $emailCampaign) }}"
                  onsubmit="return confirm('¿Añadir {{ number_format($missingCount) }} clientes nuevos como destinatarios?')">
                @csrf
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sincronizar {{ number_format($missingCount) }} clientes
                </button>
            </form>
        @endif

        @if(in_array($emailCampaign->status, ['draft', 'scheduled']))
            <form method="POST" action="{{ route('admin.email-campaigns.cancel', $emailCampaign) }}"
                  onsubmit="return confirm('¿Cancelar esta campaña?')">
                @csrf
                <button class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Cancelar campaña
                </button>
            </form>
        @endif

        <a href="{{ route('admin.email-campaigns.index') }}"
           class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ← Volver
        </a>
    </div>
</div>

{{-- Flash messages --}}
@foreach(['success'=>'green', 'error'=>'red', 'info'=>'blue'] as $type => $color)
    @if(session($type))
        <div class="mb-4 bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-xl p-4 text-sm text-{{ $color }}-800 flex items-center gap-2">
            {{ session($type) }}
        </div>
    @endif
@endforeach

{{-- Banner: clientes sin sincronizar --}}
@if($missingCount > 0 && $emailCampaign->status !== 'cancelled')
    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="text-amber-500 text-lg mt-0.5">⚠️</span>
            <div>
                <p class="text-sm font-semibold text-amber-800">
                    {{ number_format($missingCount) }} {{ $missingCount === 1 ? 'cliente' : 'clientes' }} de la campaña no {{ $missingCount === 1 ? 'está' : 'están' }} incluido{{ $missingCount === 1 ? '' : 's' }} como destinatario{{ $missingCount === 1 ? '' : 's' }}
                </p>
                <p class="text-xs text-amber-700 mt-0.5">
                    Han sido asignados a <strong>{{ $emailCampaign->campaign?->name }}</strong> pero aún no aparecen en esta campaña de email.
                </p>
            </div>
        </div>
        @if($emailCampaign->campaign_id)
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.campaigns.show', $emailCampaign->campaign_id) }}"
               class="text-xs text-amber-700 underline hover:text-amber-900 whitespace-nowrap">
                Ver campaña →
            </a>
            <form method="POST" action="{{ route('admin.email-campaigns.sync-recipients', $emailCampaign) }}"
                  onsubmit="return confirm('¿Añadir {{ number_format($missingCount) }} clientes como destinatarios?')">
                @csrf
                <button class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap">
                    Sincronizar ahora
                </button>
            </form>
        </div>
        @endif
    </div>
@endif

{{-- Queue warning --}}
@if($emailCampaign->status === 'sending' && $recipientStats['pending'] > 0)
    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <span class="text-blue-500 text-lg mt-0.5">⏳</span>
            <div>
                <p class="text-sm font-semibold text-blue-800">Campaña en cola de envío</p>
                <p class="text-xs text-blue-700 mt-1">Si lleva más de 5 minutos sin avanzar, verifica que el worker esté corriendo:</p>
                <code class="mt-1 block text-xs bg-blue-100 text-blue-800 px-3 py-1.5 rounded font-mono">php artisan queue:work</code>
            </div>
        </div>
    </div>
@endif

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($recipientStats['total']) }}</p>
        <p class="text-xs text-gray-400 mt-1">destinatarios</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Enviados</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($recipientStats['sent']) }}</p>
        @if($recipientStats['total'] > 0)
            <p class="text-xs text-gray-400 mt-1">{{ round(($recipientStats['sent'] / $recipientStats['total']) * 100, 1) }}%</p>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pendientes</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($recipientStats['pending']) }}</p>
        <p class="text-xs text-gray-400 mt-1">por enviar</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 {{ $recipientStats['failed'] > 0 ? 'ring-1 ring-red-200' : '' }}">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fallidos</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($recipientStats['failed']) }}</p>
        @if($recipientStats['failed'] > 0)
            <p class="text-xs text-red-400 mt-1">{{ round(($recipientStats['failed'] / max($recipientStats['total'],1)) * 100, 1) }}%</p>
        @else
            <p class="text-xs text-gray-400 mt-1">sin errores</p>
        @endif
    </div>
</div>

{{-- Progress bar --}}
@if($emailCampaign->total_recipients > 0)
    @php
        $sentPct   = round(($recipientStats['sent']   / $emailCampaign->total_recipients) * 100, 1);
        $failedPct = round(($recipientStats['failed'] / $emailCampaign->total_recipients) * 100, 1);
    @endphp
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-700">Progreso de envío</p>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                @if($emailCampaign->started_at)
                    <span>Inicio: {{ $emailCampaign->started_at->format('d/m H:i') }}</span>
                @endif
                @if($emailCampaign->finished_at)
                    <span>· Fin: {{ $emailCampaign->finished_at->format('d/m H:i') }}</span>
                @endif
                <span class="font-bold text-gray-700">{{ $sentPct }}%</span>
            </div>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="h-3 rounded-full flex">
                <div class="bg-green-400 h-3 transition-all" style="width: {{ $sentPct }}%"></div>
                <div class="bg-red-400  h-3 transition-all" style="width: {{ $failedPct }}%"></div>
            </div>
        </div>
        <div class="flex gap-4 mt-2 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-400"></span>Enviados ({{ $sentPct }}%)</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400"></span>Fallidos ({{ $failedPct }}%)</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-200"></span>Pendientes</span>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Campaign info --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Campaña vinculada</h3>
        @if($emailCampaign->campaign)
            <a href="{{ route('admin.campaigns.show', $emailCampaign->campaign) }}"
               class="block p-3 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition-colors">
                <p class="text-sm font-semibold text-blue-800">{{ $emailCampaign->campaign->name }}</p>
                <p class="text-xs text-blue-600 mt-0.5">Ver campaña →</p>
            </a>
        @else
            <p class="text-sm text-gray-400">Sin campaña vinculada</p>
        @endif

        <div class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-lg">
            <p class="text-xs font-medium text-gray-500">Remitente</p>
            <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $emailCampaign->from_name }}</p>
            <p class="text-xs text-gray-500">{{ $emailCampaign->from_email }}</p>
        </div>

        @if($emailCampaign->scheduled_at)
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                <p class="text-xs font-medium text-yellow-800">Programada para:</p>
                <p class="text-sm font-bold text-yellow-900">{{ $emailCampaign->scheduled_at->format('d/m/Y H:i') }}</p>
            </div>
        @endif
    </div>

    {{-- Coupon batch --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lote de cupones</h3>
        @if($emailCampaign->couponBatch)
            @php $batch = $emailCampaign->couponBatch; @endphp
            <div class="p-3 bg-green-50 border border-green-100 rounded-lg mb-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-green-800">{{ $batch->name }}</p>
                        <p class="text-xs text-green-700 mt-1">
                            {{ $batch->discount_type === 'percentage' ? $batch->discount_value . '%' : '$ ' . number_format($batch->discount_value, 0, ',', '.') }} de descuento
                        </p>
                        @if($batch->code_type === 'general')
                            <p class="text-xs text-green-700 mt-0.5">Código: <code class="bg-green-100 px-1 rounded font-mono">{{ $batch->general_code }}</code></p>
                        @else
                            <p class="text-xs text-green-700 mt-0.5">Prefijo: <code class="bg-green-100 px-1 rounded font-mono">{{ $batch->prefix }}</code></p>
                        @endif
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $batch->status === 'active' ? 'bg-green-200 text-green-800' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $batch->status === 'active' ? 'Activo' : 'Pausado' }}
                    </span>
                </div>
                @if($batch->status !== 'active')
                    <p class="text-xs text-yellow-700 mt-2 font-medium">⚠️ El lote está pausado — actívalo antes de enviar.</p>
                @endif
            </div>
        @else
            <div class="p-3 bg-yellow-50 border border-yellow-100 rounded-lg mb-3">
                <p class="text-xs text-yellow-700">Sin lote — las variables <code class="bg-yellow-100 px-1 rounded">{code}</code> y <code class="bg-yellow-100 px-1 rounded">{discount}</code> no se reemplazarán.</p>
            </div>
        @endif

        @if($availableBatches->isNotEmpty() && $emailCampaign->status !== 'cancelled')
            <form method="POST" action="{{ route('admin.email-campaigns.link-batch', $emailCampaign) }}"
                  x-data="{ open: false }">
                @csrf
                <button type="button" @click="open = !open"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium underline">
                    <span x-text="open ? 'Cancelar' : '{{ $emailCampaign->couponBatch ? 'Cambiar lote' : 'Vincular lote' }}'"></span>
                </button>
                <div x-show="open" x-transition class="mt-2 space-y-2">
                    <select name="coupon_batch_id"
                            class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-300 outline-none">
                        <option value="">— Sin lote de cupones —</option>
                        @foreach($availableBatches as $b)
                            <option value="{{ $b->id }}" {{ $emailCampaign->coupon_batch_id == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                                ({{ $b->discount_type === 'percentage' ? $b->discount_value.'%' : '$'.number_format($b->discount_value,0,',','.') }})
                                {{ $b->status !== 'active' ? '— PAUSADO' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors">
                        Guardar
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- Subject & template --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Asunto y plantilla</h3>
        <div class="p-3 bg-gray-50 border border-gray-100 rounded-lg mb-3">
            <p class="text-xs font-medium text-gray-500 mb-1">Asunto</p>
            <p class="text-sm text-gray-800">{{ $emailCampaign->subject }}</p>
        </div>
        <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 max-h-40 overflow-y-auto">
            <p class="text-xs font-mono text-gray-600 whitespace-pre-wrap">{{ Str::limit(strip_tags($emailCampaign->message_template), 300) }}</p>
        </div>
    </div>

</div>

{{-- Recipients table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Destinatarios</h3>
        <div class="flex items-center gap-3">
            <p class="text-xs text-gray-500">{{ number_format($emailCampaign->total_recipients) }} en total</p>
            @if($recipientStats['failed'] > 0)
                <form method="POST" action="{{ route('admin.email-campaigns.retry', $emailCampaign) }}"
                      onsubmit="return confirm('¿Reintentar los {{ $recipientStats['failed'] }} envíos fallidos?')">
                    @csrf
                    <button class="text-xs bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 px-3 py-1.5 rounded-lg font-medium transition-colors">
                        Reintentar {{ $recipientStats['failed'] }} fallidos
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($recipients->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-400 text-sm">No hay destinatarios registrados</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Cliente</th>
                    <th class="text-left px-4 py-3">Email</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    <th class="text-left px-4 py-3">Enviado</th>
                    <th class="text-left px-4 py-3">Cupón</th>
                    <th class="text-right px-5 py-3">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recipients as $recipient)
                <tr class="hover:bg-gray-50/60 transition-colors {{ $recipient->status === 'failed' ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3">
                        @if($recipient->customer)
                            <a href="{{ route('admin.customers.show', $recipient->customer) }}"
                               class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $recipient->customer->name }} {{ $recipient->customer->lastname }}
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">Cliente eliminado</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $recipient->email }}</td>
                    <td class="px-4 py-3">
                        @if($recipient->status === 'sent')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Enviado
                            </span>
                        @elseif($recipient->status === 'failed')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Fallido
                            </span>
                            @if($recipient->error_message)
                                <p class="text-xs text-red-500 mt-0.5 max-w-xs truncate" title="{{ $recipient->error_message }}">
                                    {{ $recipient->error_message }}
                                </p>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Pendiente
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $recipient->sent_at ? $recipient->sent_at->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs">
                        @if($recipient->assigned_coupon_code)
                            <code class="bg-green-50 text-green-700 px-1.5 py-0.5 rounded font-mono">{{ $recipient->assigned_coupon_code }}</code>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($recipient->provider_response)
                            @php
                                $pr = is_string($recipient->provider_response)
                                    ? json_decode($recipient->provider_response, true)
                                    : $recipient->provider_response;
                            @endphp
                            <button type="button"
                                    onclick="showResponse({{ $recipient->id }}, {{ json_encode(json_encode($pr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }})"
                                    class="text-xs text-blue-500 hover:text-blue-700 font-medium underline">
                                Ver respuesta
                            </button>
                        @endif
                        @if($recipient->status === 'failed')
                            <form method="POST"
                                  action="{{ route('admin.email-campaigns.retry', $emailCampaign) }}"
                                  class="inline">
                                @csrf
                                <input type="hidden" name="recipient_id" value="{{ $recipient->id }}">
                                <button class="block mt-1 text-xs text-orange-600 hover:text-orange-800 font-medium">
                                    Reintentar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Mostrando {{ $recipients->firstItem() }}–{{ $recipients->lastItem() }}
                de {{ number_format($recipients->total()) }}
            </p>
            {{ $recipients->links() }}
        </div>
    @endif
</div>

{{-- Modal respuesta proveedor --}}
<div id="response-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="absolute inset-4 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-[600px] sm:max-h-[80vh] bg-white rounded-2xl shadow-xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Respuesta del proveedor</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="flex-1 overflow-auto p-5">
            <pre id="modal-body" class="text-xs font-mono bg-gray-50 border border-gray-200 rounded-xl p-4 whitespace-pre-wrap break-all text-gray-800"></pre>
        </div>
    </div>
</div>

<script>
function showResponse(id, json) {
    document.getElementById('modal-body').textContent = json;
    document.getElementById('response-modal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('response-modal').classList.add('hidden');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

@endsection
