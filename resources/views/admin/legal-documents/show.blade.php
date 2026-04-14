@extends('layouts.admin')
@section('title', $legalDocument->title)
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.legal-documents.index') }}" class="hover:text-gray-600">Documentos Legales</a>
    <span>/</span>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $typeLabel }}</span>
</div>

{{-- Header --}}
<div class="flex items-start justify-between mb-5 gap-4">
    <div>
        <div class="flex items-center gap-3 flex-wrap mb-1">
            <h1 class="text-xl font-bold text-gray-900">{{ $legalDocument->title }}</h1>
            <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">v{{ $legalDocument->version }}</span>
            @if($legalDocument->is_active)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Vigente
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactivo
                </span>
            @endif
        </div>
        <p class="text-sm text-gray-500">
            {{ $typeLabel }}
            · Creado {{ $legalDocument->created_at->diffForHumans() }}
            @if($legalDocument->createdBy) por <strong>{{ $legalDocument->createdBy->name }}</strong>@endif
            @if($legalDocument->published_at) · Publicado {{ $legalDocument->published_at->format('d/m/Y H:i') }}@endif
        </p>
    </div>

    <div class="flex items-center gap-2 flex-shrink-0">
        @if(!$legalDocument->is_active)
            <form method="POST" action="{{ route('admin.legal-documents.publish', $legalDocument) }}">
                @csrf
                <button type="button"
                        onclick="if(confirm('¿Publicar esta versión como activa? Las otras versiones de este tipo quedarán inactivas.')) this.closest('form').submit()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                    Publicar versión
                </button>
            </form>
        @else
            <a href="{{ route('admin.legal-documents.create') }}?type={{ $legalDocument->type }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                + Nueva versión
            </a>
        @endif
        <a href="{{ route('admin.legal-documents.index') }}"
           class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ← Volver
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Contenido del documento --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Contenido del documento</h2>
                <span class="text-xs text-gray-400">{{ number_format(strlen($legalDocument->content)) }} caracteres</span>
            </div>
            <div class="px-6 py-5 prose prose-sm max-w-none text-gray-700 leading-relaxed">
                {!! $legalDocument->content !!}
            </div>
        </div>
    </div>

    {{-- Panel lateral --}}
    <div class="space-y-4">

        {{-- Stats de aceptaciones --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Aceptaciones</h3>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Total</span>
                    <span class="text-sm font-bold text-gray-900">{{ number_format($acceptanceStats['total']) }}</span>
                </div>
                @if($acceptanceStats['web'] > 0)
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Vía web</span>
                    <span class="text-sm font-medium text-blue-700">{{ number_format($acceptanceStats['web']) }}</span>
                </div>
                @endif
                @if($acceptanceStats['sms'] > 0)
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Vía SMS</span>
                    <span class="text-sm font-medium text-green-700">{{ number_format($acceptanceStats['sms']) }}</span>
                </div>
                @endif
                @if($acceptanceStats['api'] > 0)
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Vía API</span>
                    <span class="text-sm font-medium text-purple-700">{{ number_format($acceptanceStats['api']) }}</span>
                </div>
                @endif
                @if($acceptanceStats['total'] === 0)
                    <p class="text-xs text-gray-400 text-center py-2">Ninguna aceptación registrada aún.</p>
                @endif
            </div>
        </div>

        {{-- Otras versiones --}}
        @if($otherVersions->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Otras versiones</h3>
            <div class="space-y-2">
                @foreach($otherVersions as $v)
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.legal-documents.show', $v) }}"
                       class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                        <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">v{{ $v->version }}</span>
                        @if($v->is_active)
                            <span class="text-xs text-green-600">Vigente</span>
                        @else
                            <span class="text-xs text-gray-400">{{ $v->created_at->format('d/m/Y') }}</span>
                        @endif
                    </a>
                    @if(!$v->is_active)
                        <form method="POST" action="{{ route('admin.legal-documents.publish', $v) }}">
                            @csrf
                            <button type="button"
                                    onclick="if(confirm('¿Publicar v{{ $v->version }} como activa?')) this.closest('form').submit()"
                                    class="text-xs text-green-600 hover:text-green-800 font-medium">
                                Publicar
                            </button>
                        </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Páginas públicas --}}
        @php
        $publicRoutes = [
            'terms'       => 'public.legal.terms',
            'privacy'     => 'public.legal.privacy',
            'sms_consent' => 'public.legal.sms',
        ];
        @endphp
        @if(isset($publicRoutes[$legalDocument->type]) && $legalDocument->is_active)
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-blue-800 mb-2">Página pública activa</p>
            <a href="{{ route($publicRoutes[$legalDocument->type]) }}" target="_blank"
               class="text-xs text-blue-600 hover:underline break-all">
                {{ route($publicRoutes[$legalDocument->type]) }} →
            </a>
        </div>
        @endif

        {{-- Eliminar (solo inactivo sin aceptaciones) --}}
        @if(!$legalDocument->is_active && $acceptanceStats['total'] === 0)
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-700 mb-2">Este documento está inactivo y no tiene aceptaciones.</p>
            <form method="POST" action="{{ route('admin.legal-documents.destroy', $legalDocument) }}">
                @csrf @method('DELETE')
                <button type="button"
                        onclick="if(confirm('¿Eliminar esta versión permanentemente?')) this.closest('form').submit()"
                        class="w-full text-xs bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition-colors">
                    Eliminar versión
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- Tabla de aceptaciones --}}
@if($acceptanceStats['total'] > 0)
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Registro de aceptaciones</h3>
        <p class="text-xs text-gray-400">{{ number_format($acceptances->total()) }} en total</p>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="text-left px-5 py-3">Cliente</th>
                <th class="text-left px-4 py-3">Canal</th>
                <th class="text-left px-4 py-3">Fecha y hora</th>
                <th class="text-left px-4 py-3">IP</th>
                <th class="text-left px-4 py-3">User Agent</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($acceptances as $acceptance)
            <tr class="hover:bg-gray-50/60">
                <td class="px-5 py-3">
                    @if($acceptance->customer)
                        <a href="{{ route('admin.customers.show', $acceptance->customer) }}"
                           class="font-medium text-gray-800 hover:text-blue-600 text-sm">
                            {{ $acceptance->customer->name }} {{ $acceptance->customer->lastname }}
                        </a>
                        <p class="text-xs text-gray-400">{{ $acceptance->customer->phone }}</p>
                    @else
                        <span class="text-xs text-gray-400">Cliente eliminado (ID: {{ $acceptance->customer_id }})</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @php
                    $channelColors = [
                        'web' => 'bg-blue-100 text-blue-700',
                        'sms' => 'bg-green-100 text-green-700',
                        'api' => 'bg-purple-100 text-purple-700',
                    ];
                    @endphp
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $channelColors[$acceptance->channel] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ strtoupper($acceptance->channel) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    {{ \Carbon\Carbon::parse($acceptance->accepted_at)->format('d/m/Y H:i:s') }}
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-500">
                    {{ $acceptance->ip_address ?? '—' }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-400 max-w-[220px]">
                    @if($acceptance->user_agent)
                        <span class="truncate block" title="{{ $acceptance->user_agent }}">
                            {{ Str::limit($acceptance->user_agent, 50) }}
                        </span>
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($acceptances->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">
            Mostrando {{ $acceptances->firstItem() }}–{{ $acceptances->lastItem() }}
            de {{ number_format($acceptances->total()) }}
        </p>
        {{ $acceptances->links() }}
    </div>
    @endif
</div>
@endif

@endsection
