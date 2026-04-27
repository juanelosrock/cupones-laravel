@extends('layouts.admin')
@section('title', 'Campañas WhatsApp')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Campañas WhatsApp</h1>
        <p class="text-sm text-gray-500 mt-0.5">Envíos masivos por WhatsApp vía Zenvia</p>
    </div>
    <a href="{{ route('admin.whatsapp-campaigns.create') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        + Nueva Campaña WhatsApp
    </a>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total campañas</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['sent'] }} enviadas</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Destinatarios totales</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['recipients']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ number_format($stats['sent_total']) }} enviados</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">En progreso</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['sending'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['scheduled'] }} programadas</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Borradores</p>
        <p class="text-2xl font-bold text-gray-600 mt-1">{{ $stats['draft'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['failed'] }} fallidas/canceladas</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nombre de la campaña..."
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">Todos</option>
                @foreach(['draft'=>'Borrador','scheduled'=>'Programada','sending'=>'Enviando','sent'=>'Enviada','failed'=>'Fallida','cancelled'=>'Cancelada'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Campaña</label>
            <select name="campaign_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">Todas</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c->id }}" {{ request('campaign_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
            @if(request()->hasAny(['search','status','campaign_id']))
                <a href="{{ route('admin.whatsapp-campaigns.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Limpiar</a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($waCampaigns->isEmpty())
        <div class="text-center py-16">
            <div class="text-5xl mb-4">💬</div>
            <p class="text-gray-500 font-medium">No hay campañas WhatsApp creadas</p>
            <a href="{{ route('admin.whatsapp-campaigns.create') }}"
               class="mt-3 inline-block text-green-600 hover:text-green-800 text-sm font-medium">
                + Crear primera campaña
            </a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Campaña WhatsApp</th>
                    <th class="text-left px-4 py-3">Campaña vinculada</th>
                    <th class="text-left px-4 py-3">Destinatarios</th>
                    <th class="text-left px-4 py-3">Progreso</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    <th class="text-left px-4 py-3">Fecha</th>
                    <th class="text-right px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($waCampaigns as $wa)
                @php
                    $colors = ['draft'=>'bg-gray-100 text-gray-600','scheduled'=>'bg-yellow-100 text-yellow-700','sending'=>'bg-green-100 text-green-700','sent'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-100 text-gray-500'];
                    $labels = ['draft'=>'Borrador','scheduled'=>'Programada','sending'=>'Enviando','sent'=>'Enviada','failed'=>'Fallida','cancelled'=>'Cancelada'];
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $wa->name }}</p>
                        @if($wa->couponBatch)
                            <p class="text-xs text-green-600 mt-0.5">Lote: {{ $wa->couponBatch->name }}</p>
                        @endif
                        @if($wa->scheduled_at && in_array($wa->status, ['scheduled','draft']))
                            <p class="text-xs text-yellow-600 mt-0.5">Programada: {{ $wa->scheduled_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($wa->campaign)
                            <a href="{{ route('admin.campaigns.show', $wa->campaign) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">{{ $wa->campaign->name }}</a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm font-semibold text-gray-800">{{ number_format($wa->total_recipients) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($wa->total_recipients > 0)
                            @php $pct = round(($wa->sent_count / $wa->total_recipients) * 100); @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5 w-20">
                                    <div class="{{ $wa->failed_count > 0 ? 'bg-red-400' : 'bg-green-400' }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $pct }}%</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $wa->sent_count }} enviados · {{ $wa->failed_count }} fallidos</p>
                        @else
                            <span class="text-xs text-gray-400">Sin destinatarios</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$wa->status] ?? 'bg-gray-100 text-gray-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $wa->status === 'sent' ? 'bg-green-500' : ($wa->status === 'sending' ? 'bg-green-500 animate-pulse' : ($wa->status === 'failed' ? 'bg-red-500' : ($wa->status === 'scheduled' ? 'bg-yellow-500' : 'bg-gray-400'))) }}"></span>
                            {{ $labels[$wa->status] ?? $wa->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $wa->created_at->format('d/m/Y') }}
                        @if($wa->finished_at)
                            <br><span class="text-green-600">Fin: {{ $wa->finished_at->format('d/m H:i') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.whatsapp-campaigns.show', $wa) }}"
                               class="text-xs text-green-600 hover:text-green-800 font-medium">Ver</a>
                            @if(in_array($wa->status, ['draft','scheduled','failed']))
                                <span class="text-gray-200">|</span>
                                <form method="POST" action="{{ route('admin.whatsapp-campaigns.send', $wa) }}"
                                      onsubmit="return confirm('¿Enviar la campaña «{{ addslashes($wa->name) }}» a {{ number_format($wa->total_recipients) }} destinatarios?')">
                                    @csrf
                                    <button class="text-xs text-green-600 hover:text-green-800 font-medium">Enviar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Mostrando {{ $waCampaigns->firstItem() }}–{{ $waCampaigns->lastItem() }}
                de {{ number_format($waCampaigns->total()) }} campañas
            </p>
            {{ $waCampaigns->links() }}
        </div>
    @endif
</div>

@endsection
