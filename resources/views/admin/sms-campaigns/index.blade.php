@extends('layouts.admin')
@section('title', 'Campañas SMS')
@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Campañas SMS</h1>
        <p class="text-sm text-gray-500 mt-0.5">Gestiona envíos masivos de SMS con cupones de descuento</p>
    </div>
    <a href="{{ route('admin.sms-campaigns.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        + Nueva Campaña SMS
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
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['recipients']) }}</p>
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
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Borrador</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Programada</option>
                <option value="sending"   {{ request('status') === 'sending'   ? 'selected' : '' }}>Enviando</option>
                <option value="sent"      {{ request('status') === 'sent'      ? 'selected' : '' }}>Enviada</option>
                <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Fallida</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Campaña</label>
            <select name="campaign_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c->id }}" {{ request('campaign_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
            @if(request()->hasAny(['search', 'status', 'campaign_id']))
                <a href="{{ route('admin.sms-campaigns.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Limpiar</a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($smsCampaigns->isEmpty())
        <div class="text-center py-16">
            <div class="text-5xl mb-4">📱</div>
            <p class="text-gray-500 font-medium">No hay campañas SMS creadas</p>
            <a href="{{ route('admin.sms-campaigns.create') }}"
               class="mt-3 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                + Crear primera campaña
            </a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Campaña SMS</th>
                    <th class="text-left px-4 py-3">Campaña vinculada</th>
                    <th class="text-left px-4 py-3">Destinatarios</th>
                    <th class="text-left px-4 py-3">Progreso</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    <th class="text-left px-4 py-3">Fecha</th>
                    <th class="text-right px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($smsCampaigns as $sms)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $sms->name }}</p>
                        @if($sms->couponBatch)
                            <p class="text-xs text-blue-600 mt-0.5">Lote: {{ $sms->couponBatch->name }}</p>
                        @endif
                        @if($sms->scheduled_at && in_array($sms->status, ['scheduled','draft']))
                            <p class="text-xs text-yellow-600 mt-0.5">Programada: {{ $sms->scheduled_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($sms->campaign)
                            <a href="{{ route('admin.campaigns.show', $sms->campaign) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                {{ $sms->campaign->name }}
                            </a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm font-semibold text-gray-800">{{ number_format($sms->total_recipients) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($sms->total_recipients > 0)
                            @php
                                $pct = round(($sms->sent_count / $sms->total_recipients) * 100);
                                $barColor = $sms->failed_count > 0 ? 'bg-red-400' : 'bg-green-400';
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5 w-20">
                                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $pct }}%</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $sms->sent_count }} enviados · {{ $sms->failed_count }} fallidos</p>
                        @else
                            <span class="text-xs text-gray-400">Sin destinatarios</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $colors = [
                                'draft'     => 'bg-gray-100 text-gray-600',
                                'scheduled' => 'bg-yellow-100 text-yellow-700',
                                'sending'   => 'bg-blue-100 text-blue-700',
                                'sent'      => 'bg-green-100 text-green-700',
                                'failed'    => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-100 text-gray-500',
                            ];
                            $labels = [
                                'draft'     => 'Borrador',
                                'scheduled' => 'Programada',
                                'sending'   => 'Enviando',
                                'sent'      => 'Enviada',
                                'failed'    => 'Fallida',
                                'cancelled' => 'Cancelada',
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$sms->status] ?? 'bg-gray-100 text-gray-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full
                                {{ $sms->status === 'sent'      ? 'bg-green-500' :
                                   ($sms->status === 'sending'  ? 'bg-blue-500 animate-pulse' :
                                   ($sms->status === 'failed'   ? 'bg-red-500' :
                                   ($sms->status === 'scheduled'? 'bg-yellow-500' : 'bg-gray-400'))) }}">
                            </span>
                            {{ $labels[$sms->status] ?? $sms->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $sms->created_at->format('d/m/Y') }}
                        @if($sms->finished_at)
                            <br><span class="text-green-600">Fin: {{ $sms->finished_at->format('d/m H:i') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                            <a href="{{ route('admin.sms-campaigns.show', $sms) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">Ver</a>
                            @if(in_array($sms->status, ['draft', 'scheduled', 'failed']))
                                <span class="text-gray-200">|</span>
                                <form method="POST" action="{{ route('admin.sms-campaigns.send', $sms) }}"
                                      onsubmit="return confirm('¿Enviar la campaña «{{ addslashes($sms->name) }}» a {{ number_format($sms->total_recipients) }} destinatarios?')">
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
                Mostrando {{ $smsCampaigns->firstItem() }}–{{ $smsCampaigns->lastItem() }}
                de {{ number_format($smsCampaigns->total()) }} campañas
            </p>
            {{ $smsCampaigns->links() }}
        </div>
    @endif
</div>

@endsection
