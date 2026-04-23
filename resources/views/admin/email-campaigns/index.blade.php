@extends('layouts.admin')
@section('title', 'Campañas de Email')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Campañas de Email</h1>
        <p class="text-sm text-gray-500 mt-0.5">Envíos masivos de correo electrónico vía Zenvia</p>
    </div>
    <a href="{{ route('admin.email-campaigns.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        + Nueva campaña
    </a>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif

{{-- KPI Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-400 mt-1">campañas</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Enviadas</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['sent']) }}</p>
        <p class="text-xs text-gray-400 mt-1">completadas</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Destinatarios</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['recipients']) }}</p>
        <p class="text-xs text-gray-400 mt-1">total acumulado</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Emails enviados</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($stats['sent_total']) }}</p>
        <p class="text-xs text-gray-400 mt-1">entregados</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre de la campaña…"
               class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-56 focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
            <option value="">Todos</option>
            @foreach(['draft'=>'Borrador','scheduled'=>'Programada','sending'=>'Enviando','sent'=>'Enviada','failed'=>'Fallida','cancelled'=>'Cancelada'] as $v => $l)
                <option value="{{ $v }}" {{ request('status') == $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Campaña</label>
        <select name="campaign_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
            <option value="">Todas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}" {{ request('campaign_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Filtrar</button>
    @if(request()->hasAny(['search','status','campaign_id']))
        <a href="{{ route('admin.email-campaigns.index') }}" class="text-sm text-gray-400 hover:text-gray-600 self-end pb-2">Limpiar</a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($emailCampaigns->isEmpty())
        <div class="text-center py-16">
            <p class="text-4xl mb-3">📧</p>
            <p class="text-gray-500 font-medium">No hay campañas de email</p>
            <p class="text-sm text-gray-400 mt-1">Crea tu primera campaña para comenzar</p>
            <a href="{{ route('admin.email-campaigns.create') }}"
               class="mt-4 inline-block bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                + Nueva campaña
            </a>
        </div>
    @else
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
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Nombre</th>
                    <th class="text-left px-4 py-3">Campaña</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    <th class="text-right px-4 py-3">Destinatarios</th>
                    <th class="text-right px-4 py-3">Enviados</th>
                    <th class="text-left px-4 py-3">Creada</th>
                    <th class="text-right px-5 py-3">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($emailCampaigns as $ec)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.email-campaigns.show', $ec) }}"
                           class="font-semibold text-gray-800 hover:text-blue-600">{{ $ec->name }}</a>
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $ec->subject }}</p>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        {{ $ec->campaign?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$ec->status] ?? 'bg-gray-100 text-gray-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full
                                {{ $ec->status === 'sent' ? 'bg-green-500' :
                                   ($ec->status === 'sending' ? 'bg-blue-500 animate-pulse' :
                                   ($ec->status === 'failed' ? 'bg-red-500' :
                                   ($ec->status === 'scheduled' ? 'bg-yellow-500' : 'bg-gray-400'))) }}">
                            </span>
                            {{ $statusLabels[$ec->status] ?? $ec->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-gray-700">{{ number_format($ec->total_recipients) }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($ec->total_recipients > 0)
                            <span class="font-medium text-green-600">{{ number_format($ec->sent_count) }}</span>
                            <span class="text-xs text-gray-400"> / {{ number_format($ec->total_recipients) }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $ec->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.email-campaigns.show', $ec) }}"
                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">Ver →</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $emailCampaigns->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
