@extends('layouts.admin')
@section('title', $smsCampaign->name)
@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.sms-campaigns.index') }}" class="hover:text-gray-600">Campañas SMS</a>
    <span>/</span>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $smsCampaign->name }}</span>
</div>

{{-- Header --}}
<div class="flex items-start justify-between mb-4">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <h1 class="text-2xl font-bold text-gray-900">{{ $smsCampaign->name }}</h1>
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
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$smsCampaign->status] ?? 'bg-gray-100 text-gray-600' }}">
                <span class="w-1.5 h-1.5 rounded-full
                    {{ $smsCampaign->status === 'sent'      ? 'bg-green-500' :
                       ($smsCampaign->status === 'sending'  ? 'bg-blue-500 animate-pulse' :
                       ($smsCampaign->status === 'failed'   ? 'bg-red-500' :
                       ($smsCampaign->status === 'scheduled'? 'bg-yellow-500' : 'bg-gray-400'))) }}">
                </span>
                {{ $statusLabels[$smsCampaign->status] ?? $smsCampaign->status }}
            </span>
        </div>
        <p class="text-sm text-gray-500">
            Creada {{ $smsCampaign->created_at->diffForHumans() }}
            @if($smsCampaign->createdBy) por <strong>{{ $smsCampaign->createdBy->name }}</strong>@endif
        </p>
    </div>

    <div class="flex items-center gap-2 flex-wrap justify-end">
        {{-- Enviar (borrador / programada / fallida) --}}
        @if(in_array($smsCampaign->status, ['draft', 'scheduled', 'failed']))
            <form method="POST" action="{{ route('admin.sms-campaigns.send', $smsCampaign) }}"
                  onsubmit="return confirm('¿Confirmas el envío de {{ number_format($smsCampaign->total_recipients) }} SMS?')">
                @csrf
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Enviar ahora
                </button>
            </form>
        @endif

        {{-- Reintentar fallidos --}}
        @if($recipientStats['failed'] > 0)
            <form method="POST" action="{{ route('admin.sms-campaigns.retry', $smsCampaign) }}"
                  onsubmit="return confirm('¿Reintentar los {{ number_format($recipientStats['failed']) }} envíos fallidos?')">
                @csrf
                <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Reintentar fallidos ({{ number_format($recipientStats['failed']) }})
                </button>
            </form>
        @endif

        {{-- Reenviar toda la campaña (ya enviada) --}}
        @if($smsCampaign->status === 'sent' && $recipientStats['failed'] === 0)
            <form method="POST" action="{{ route('admin.sms-campaigns.retry', $smsCampaign) }}"
                  onsubmit="return confirm('¿Reenviar la campaña completa?\nSolo se enviarán los mensajes con estado pendiente o fallido.')">
                @csrf
                <button class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Reenviar
                </button>
            </form>
        @endif

        {{-- Cancelar --}}
        @if(in_array($smsCampaign->status, ['draft', 'scheduled']))
            <form method="POST" action="{{ route('admin.sms-campaigns.cancel', $smsCampaign) }}"
                  onsubmit="return confirm('¿Cancelar esta campaña?')">
                @csrf
                <button class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Cancelar campaña
                </button>
            </form>
        @endif

        <a href="{{ route('admin.sms-campaigns.index') }}"
           class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ← Volver
        </a>
    </div>
</div>

{{-- Alertas --}}
@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif

{{-- Queue warning: mostrar si hay pendientes pero no está running --}}
@if($smsCampaign->status === 'sending' && $recipientStats['pending'] > 0)
    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <span class="text-blue-500 text-lg mt-0.5">⏳</span>
            <div>
                <p class="text-sm font-semibold text-blue-800">Campaña en cola de envío</p>
                <p class="text-xs text-blue-700 mt-1">
                    El proceso de envío está activo. Si lleva más de 5 minutos sin avanzar, asegúrate de que el worker esté corriendo:
                </p>
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
            <p class="text-xs text-gray-400 mt-1">{{ round(($recipientStats['sent'] / $recipientStats['total']) * 100, 1) }}% del total</p>
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
            <p class="text-xs text-red-400 mt-1">{{ round(($recipientStats['failed'] / $recipientStats['total']) * 100, 1) }}% de error</p>
        @else
            <p class="text-xs text-gray-400 mt-1">sin errores</p>
        @endif
    </div>
</div>

{{-- Consent stats (solo si la campaña usa consentimiento) --}}
@if($smsCampaign->send_consent_link)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5">
        <div class="flex items-center gap-3 mb-3">
            <span class="text-blue-500 text-lg">🔒</span>
            <h3 class="text-sm font-semibold text-blue-800">Consentimiento de datos</h3>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Campaña con autorización</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="bg-white rounded-lg p-3 border border-blue-100">
                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">SMS enviados</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($recipientStats['sent']) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">con enlace de autorización</p>
            </div>
            <div class="bg-white rounded-lg p-3 border border-green-200">
                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Autorizaciones</p>
                <p class="text-xl font-bold text-green-600 mt-1">{{ number_format($recipientStats['consent_accepted']) }}</p>
                @if($recipientStats['sent'] > 0)
                    <p class="text-xs text-green-600 mt-0.5">{{ round(($recipientStats['consent_accepted'] / $recipientStats['sent']) * 100, 1) }}% tasa de aceptación</p>
                @else
                    <p class="text-xs text-gray-400 mt-0.5">aceptaron datos</p>
                @endif
            </div>
            <div class="bg-white rounded-lg p-3 border border-yellow-200">
                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Pendientes</p>
                <p class="text-xl font-bold text-yellow-600 mt-1">{{ number_format($recipientStats['consent_pending']) }}</p>
                <p class="text-xs text-yellow-600 mt-0.5">no han aceptado aún</p>
            </div>
        </div>
        <p class="text-xs text-blue-600 mt-3">
            Los clientes que acepten verán su código de descuento al instante. Las aceptaciones quedan registradas en auditoría (Ley 1581).
        </p>
    </div>
@endif

{{-- Progress bar --}}
@if($smsCampaign->total_recipients > 0)
    @php
        $sentPct   = round(($recipientStats['sent']   / $smsCampaign->total_recipients) * 100, 1);
        $failedPct = round(($recipientStats['failed'] / $smsCampaign->total_recipients) * 100, 1);
    @endphp
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-700">Progreso de envío</p>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                @if($smsCampaign->started_at)
                    <span>Inicio: {{ $smsCampaign->started_at->format('d/m H:i') }}</span>
                @endif
                @if($smsCampaign->finished_at)
                    <span>·</span>
                    <span>Fin: {{ $smsCampaign->finished_at->format('d/m H:i') }}</span>
                    <span>·</span>
                    <span>Duración: {{ $smsCampaign->started_at->diffForHumans($smsCampaign->finished_at, true) }}</span>
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
        @if($smsCampaign->campaign)
            <a href="{{ route('admin.campaigns.show', $smsCampaign->campaign) }}"
               class="block p-3 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition-colors">
                <p class="text-sm font-semibold text-blue-800">{{ $smsCampaign->campaign->name }}</p>
                <p class="text-xs text-blue-600 mt-0.5">Ver campaña →</p>
            </a>
        @else
            <p class="text-sm text-gray-400">Sin campaña vinculada</p>
        @endif

        @if($smsCampaign->scheduled_at)
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                <p class="text-xs font-medium text-yellow-800">Programada para:</p>
                <p class="text-sm font-bold text-yellow-900">{{ $smsCampaign->scheduled_at->format('d/m/Y H:i') }}</p>
            </div>
        @endif
    </div>

    {{-- Coupon batch --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lote de cupones</h3>
        @if($smsCampaign->couponBatch)
            @php $batch = $smsCampaign->couponBatch; @endphp
            <div class="p-3 bg-green-50 border border-green-100 rounded-lg">
                <p class="text-sm font-semibold text-green-800">{{ $batch->name }}</p>
                <p class="text-xs text-green-700 mt-1">
                    {{ $batch->discount_type === 'percentage' ? $batch->discount_value . '%' : '$ ' . number_format($batch->discount_value, 0, ',', '.') }} de descuento
                </p>
                @if($batch->code_type === 'general')
                    <p class="text-xs text-green-700 mt-0.5">Código: <code class="bg-green-100 px-1 rounded font-mono">{{ $batch->general_code }}</code></p>
                @else
                    <p class="text-xs text-green-700 mt-0.5">Códigos únicos — prefijo: <code class="bg-green-100 px-1 rounded font-mono">{{ $batch->prefix }}</code></p>
                @endif
            </div>
        @else
            <div class="p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                <p class="text-xs text-yellow-700">Sin lote de cupones — las variables <code class="bg-yellow-100 px-1 rounded">{code}</code> y <code class="bg-yellow-100 px-1 rounded">{discount}</code> no se reemplazarán.</p>
            </div>
        @endif
    </div>

    {{-- Message template --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Plantilla del mensaje</h3>
        <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
            <p class="text-sm text-gray-800 leading-relaxed">{{ $smsCampaign->message_template }}</p>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ strlen($smsCampaign->message_template) }}/160 caracteres</p>
    </div>

</div>

{{-- Recipients table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Destinatarios</h3>
        <div class="flex items-center gap-3">
            <p class="text-xs text-gray-500">{{ number_format($smsCampaign->total_recipients) }} en total</p>
            @if($recipientStats['failed'] > 0)
                <form method="POST" action="{{ route('admin.sms-campaigns.retry', $smsCampaign) }}"
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
                    <th class="text-left px-4 py-3">Teléfono</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    @if($smsCampaign->send_consent_link)
                    <th class="text-left px-4 py-3">Consentimiento</th>
                    @endif
                    <th class="text-left px-4 py-3">Enviado</th>
                    <th class="text-left px-4 py-3">Mensaje enviado</th>
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
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $recipient->phone }}</td>
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
                    @if($smsCampaign->send_consent_link)
                    <td class="px-4 py-3">
                        @if($recipient->consent_accepted_at)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aceptó
                            </span>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $recipient->consent_accepted_at->format('d/m H:i') }}</p>
                        @elseif($recipient->status === 'sent')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>Pendiente
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    @endif
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $recipient->sent_at ? $recipient->sent_at->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 max-w-xs">
                        @if($recipient->message_sent)
                            <span class="truncate block max-w-[220px]" title="{{ $recipient->message_sent }}">
                                {{ $recipient->message_sent }}
                            </span>
                            {{-- Zenvia message ID --}}
                            @php
                                $pr = is_string($recipient->provider_response)
                                    ? json_decode($recipient->provider_response, true)
                                    : $recipient->provider_response;
                                $msgId = $pr['message_id'] ?? null;
                            @endphp
                            @if($msgId)
                                <span class="text-gray-400 font-mono" title="Zenvia message_id">ID: {{ substr($msgId, 0, 8) }}…</span>
                            @endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($recipient->provider_response)
                            @php
                                $prFull = is_string($recipient->provider_response)
                                    ? json_decode($recipient->provider_response, true)
                                    : $recipient->provider_response;
                            @endphp
                            <button type="button"
                                    onclick="showResponse({{ $recipient->id }}, {{ json_encode(json_encode($prFull, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }})"
                                    class="text-xs text-blue-500 hover:text-blue-700 font-medium underline">
                                Ver respuesta
                            </button>
                        @endif
                        @if($recipient->status === 'failed')
                            <form method="POST"
                                  action="{{ route('admin.sms-campaigns.recipients.retry', [$smsCampaign, $recipient]) }}">
                                @csrf
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

{{-- Modal: respuesta completa del proveedor --}}
<div id="response-modal" class="fixed inset-0 z-50 hidden" aria-modal="true">
    <div class="absolute inset-0 bg-black/50" onclick="closeResponseModal()"></div>
    <div class="absolute inset-4 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-[600px] sm:max-h-[80vh] bg-white rounded-2xl shadow-xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Respuesta del proveedor</h3>
                <p id="response-modal-subtitle" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeResponseModal()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="flex-1 overflow-auto p-5">
            <pre id="response-modal-body"
                 class="text-xs font-mono bg-gray-50 border border-gray-200 rounded-xl p-4 whitespace-pre-wrap break-all text-gray-800"></pre>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 flex justify-end">
            <button type="button"
                    onclick="copyResponseToClipboard()"
                    class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                Copiar JSON
            </button>
        </div>
    </div>
</div>

<script>
function showResponse(recipientId, json) {
    document.getElementById('response-modal-subtitle').textContent = 'Destinatario #' + recipientId;
    document.getElementById('response-modal-body').textContent = json;
    document.getElementById('response-modal').classList.remove('hidden');
}
function closeResponseModal() {
    document.getElementById('response-modal').classList.add('hidden');
}
function copyResponseToClipboard() {
    const text = document.getElementById('response-modal-body').textContent;
    navigator.clipboard?.writeText(text) ?? (function() {
        const ta = document.createElement('textarea');
        ta.value = text; document.body.appendChild(ta); ta.select();
        document.execCommand('copy'); document.body.removeChild(ta);
    })();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeResponseModal(); });
</script>

@endsection
