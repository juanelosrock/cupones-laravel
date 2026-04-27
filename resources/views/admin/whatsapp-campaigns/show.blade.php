@extends('layouts.admin')
@section('title', $whatsAppCampaign->name)
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.whatsapp-campaigns.index') }}" class="hover:text-gray-600">Campañas WhatsApp</a>
    <span>/</span>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $whatsAppCampaign->name }}</span>
</div>

@php
    $statusColors = ['draft'=>'bg-gray-100 text-gray-600','scheduled'=>'bg-yellow-100 text-yellow-700','sending'=>'bg-green-100 text-green-700','sent'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-100 text-gray-500'];
    $statusLabels = ['draft'=>'Borrador','scheduled'=>'Programada','sending'=>'Enviando','sent'=>'Enviada','failed'=>'Fallida','cancelled'=>'Cancelada'];
@endphp

<div class="flex items-start justify-between mb-4">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ $whatsAppCampaign->name }}</h1>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$whatsAppCampaign->status] ?? 'bg-gray-100 text-gray-600' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $whatsAppCampaign->status === 'sent' ? 'bg-green-500' : ($whatsAppCampaign->status === 'sending' ? 'bg-green-500 animate-pulse' : ($whatsAppCampaign->status === 'failed' ? 'bg-red-500' : ($whatsAppCampaign->status === 'scheduled' ? 'bg-yellow-500' : 'bg-gray-400'))) }}"></span>
                {{ $statusLabels[$whatsAppCampaign->status] ?? $whatsAppCampaign->status }}
            </span>
        </div>
        <p class="text-sm text-gray-500">
            Creada {{ $whatsAppCampaign->created_at->diffForHumans() }}
            @if($whatsAppCampaign->createdBy) por <strong>{{ $whatsAppCampaign->createdBy->name }}</strong>@endif
        </p>
    </div>

    <div class="flex items-center gap-2 flex-wrap justify-end">
        @if(in_array($whatsAppCampaign->status, ['draft','scheduled','failed']))
            <form method="POST" action="{{ route('admin.whatsapp-campaigns.send', $whatsAppCampaign) }}"
                  onsubmit="return confirm('¿Confirmas el envío de {{ number_format($whatsAppCampaign->total_recipients) }} mensajes WhatsApp?')">
                @csrf
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Enviar ahora
                </button>
            </form>
        @endif

        @if($recipientStats['failed'] > 0)
            <form method="POST" action="{{ route('admin.whatsapp-campaigns.retry', $whatsAppCampaign) }}"
                  onsubmit="return confirm('¿Reintentar los {{ number_format($recipientStats['failed']) }} envíos fallidos?')">
                @csrf
                <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Reintentar fallidos ({{ number_format($recipientStats['failed']) }})
                </button>
            </form>
        @endif

        @if(in_array($whatsAppCampaign->status, ['draft','scheduled']))
            <form method="POST" action="{{ route('admin.whatsapp-campaigns.cancel', $whatsAppCampaign) }}"
                  onsubmit="return confirm('¿Cancelar esta campaña?')">
                @csrf
                <button class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Cancelar campaña
                </button>
            </form>
        @endif

        @if($missingCount > 0 && $whatsAppCampaign->status !== 'cancelled')
            <form method="POST" action="{{ route('admin.whatsapp-campaigns.sync-recipients', $whatsAppCampaign) }}"
                  onsubmit="return confirm('¿Añadir {{ number_format($missingCount) }} clientes nuevos?')">
                @csrf
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Sincronizar {{ number_format($missingCount) }} clientes
                </button>
            </form>
        @endif

        <a href="{{ route('admin.whatsapp-campaigns.index') }}"
           class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ← Volver
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif
@if(session('info'))
    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">ℹ️ {{ session('info') }}</div>
@endif

@if($whatsAppCampaign->status === 'sending' && $recipientStats['pending'] > 0)
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start gap-3">
        <span class="text-green-500 text-lg mt-0.5">⏳</span>
        <div>
            <p class="text-sm font-semibold text-green-800">Campaña en cola de envío</p>
            <p class="text-xs text-green-700 mt-1">Si lleva más de 5 minutos sin avanzar, verifica que el worker esté activo:</p>
            <code class="mt-1 block text-xs bg-green-100 text-green-800 px-3 py-1.5 rounded font-mono">php artisan queue:work</code>
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
        <p class="text-xs {{ $recipientStats['failed'] > 0 ? 'text-red-400' : 'text-gray-400' }} mt-1">
            {{ $recipientStats['failed'] > 0 && $recipientStats['total'] > 0 ? round(($recipientStats['failed'] / $recipientStats['total']) * 100, 1) . '% error' : 'sin errores' }}
        </p>
    </div>
</div>

{{-- Progress bar --}}
@if($whatsAppCampaign->total_recipients > 0)
    @php
        $sentPct   = round(($recipientStats['sent']   / $whatsAppCampaign->total_recipients) * 100, 1);
        $failedPct = round(($recipientStats['failed'] / $whatsAppCampaign->total_recipients) * 100, 1);
    @endphp
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-700">Progreso de envío</p>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                @if($whatsAppCampaign->started_at)
                    <span>Inicio: {{ $whatsAppCampaign->started_at->format('d/m H:i') }}</span>
                @endif
                @if($whatsAppCampaign->finished_at)
                    <span>· Fin: {{ $whatsAppCampaign->finished_at->format('d/m H:i') }}</span>
                @endif
                <span class="font-bold text-gray-700">{{ $sentPct }}%</span>
            </div>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="h-3 rounded-full flex">
                <div class="bg-green-400 h-3 transition-all" style="width: {{ $sentPct }}%"></div>
                <div class="bg-red-400 h-3 transition-all" style="width: {{ $failedPct }}%"></div>
            </div>
        </div>
        <div class="flex gap-4 mt-2 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-400"></span>Enviados</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400"></span>Fallidos</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-200"></span>Pendientes</span>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    {{-- Campaña --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Campaña vinculada</h3>
        @if($whatsAppCampaign->campaign)
            <a href="{{ route('admin.campaigns.show', $whatsAppCampaign->campaign) }}"
               class="block p-3 bg-green-50 border border-green-100 rounded-lg hover:bg-green-100 transition-colors">
                <p class="text-sm font-semibold text-green-800">{{ $whatsAppCampaign->campaign->name }}</p>
                <p class="text-xs text-green-600 mt-0.5">Ver campaña →</p>
            </a>
        @else
            <p class="text-sm text-gray-400">Sin campaña vinculada</p>
        @endif
        @if($whatsAppCampaign->scheduled_at)
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                <p class="text-xs font-medium text-yellow-800">Programada para:</p>
                <p class="text-sm font-bold text-yellow-900">{{ $whatsAppCampaign->scheduled_at->format('d/m/Y H:i') }}</p>
            </div>
        @endif
    </div>

    {{-- Lote --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lote de cupones</h3>
        @if($whatsAppCampaign->couponBatch)
            @php $batch = $whatsAppCampaign->couponBatch; @endphp
            <div class="p-3 bg-green-50 border border-green-100 rounded-lg mb-3">
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
        @else
            <div class="p-3 bg-yellow-50 border border-yellow-100 rounded-lg mb-3">
                <p class="text-xs text-yellow-700">Sin lote — <code class="bg-yellow-100 px-1 rounded">{code}</code> y <code class="bg-yellow-100 px-1 rounded">{discount}</code> no se reemplazarán.</p>
            </div>
        @endif

        @if($availableBatches->isNotEmpty() && $whatsAppCampaign->status !== 'cancelled')
            <form method="POST" action="{{ route('admin.whatsapp-campaigns.link-batch', $whatsAppCampaign) }}"
                  x-data="{ open: false }">
                @csrf
                <button type="button" @click="open = !open"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium underline">
                    <span x-text="open ? 'Cancelar' : '{{ $whatsAppCampaign->couponBatch ? 'Cambiar lote' : 'Vincular lote' }}'"></span>
                </button>
                <div x-show="open" x-transition class="mt-2 space-y-2">
                    <select name="coupon_batch_id"
                            class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-green-300 outline-none">
                        <option value="">— Sin lote —</option>
                        @foreach($availableBatches as $b)
                            <option value="{{ $b->id }}" {{ $whatsAppCampaign->coupon_batch_id == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->discount_type === 'percentage' ? $b->discount_value.'%' : '$'.number_format($b->discount_value,0,',','.') }})
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors">
                        Guardar
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- Plantilla --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Plantilla del mensaje</h3>
        <div class="bg-[#dcf8c6] rounded-lg p-3 shadow-sm">
            <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $whatsAppCampaign->message_template }}</p>
            <p class="text-right text-[10px] text-gray-400 mt-1">✓✓</p>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ strlen($whatsAppCampaign->message_template) }} caracteres</p>
    </div>
</div>

{{-- Recipients table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Destinatarios</h3>
        <p class="text-xs text-gray-500">{{ number_format($whatsAppCampaign->total_recipients) }} en total</p>
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
                    <th class="text-left px-4 py-3">Teléfono</th>
                    <th class="text-left px-4 py-3">Código</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    <th class="text-left px-4 py-3">Enviado</th>
                    <th class="text-right px-5 py-3">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recipients as $recipient)
                <tr class="hover:bg-gray-50/60 transition-colors {{ $recipient->status === 'failed' ? 'bg-red-50/30' : '' }}">
                    <td class="px-5 py-3">
                        @if($recipient->customer)
                            <a href="{{ route('admin.customers.show', $recipient->customer) }}"
                               class="font-medium text-gray-800 hover:text-green-600">
                                {{ $recipient->customer->name }} {{ $recipient->customer->lastname }}
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $recipient->phone }}</td>
                    <td class="px-4 py-3">
                        @if($recipient->assigned_coupon_code)
                            <code class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded font-mono">{{ $recipient->assigned_coupon_code }}</code>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
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
                    <td class="px-5 py-3 text-right">
                        @if($recipient->status === 'failed')
                            <form method="POST"
                                  action="{{ route('admin.whatsapp-campaigns.recipients.retry', [$whatsAppCampaign, $recipient]) }}">
                                @csrf
                                <button class="text-xs text-orange-600 hover:text-orange-800 font-medium">
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

@endsection
