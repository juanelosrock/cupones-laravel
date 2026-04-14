@extends('layouts.admin')
@section('title', 'Documentos Legales')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Documentos Legales</h1>
        <p class="text-sm text-gray-500 mt-1">Gestión de versiones de T&C, privacidad y consentimientos (Ley 1581).</p>
    </div>
    <a href="{{ route('admin.legal-documents.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        + Nueva versión
    </a>
</div>

@if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Versiones</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-400 mt-0.5">documentos totales</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vigentes</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p>
        <p class="text-xs text-gray-400 mt-0.5">versiones activas</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tipos</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['types'] }}</p>
        <p class="text-xs text-gray-400 mt-0.5">categorías</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Aceptaciones</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($stats['accepted']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">registros totales</p>
    </div>
</div>

@php
$typeConfig = [
    'terms'       => ['label' => 'Términos y Condiciones',     'icon' => '📋', 'color' => 'blue',   'public_route' => 'public.legal.terms'],
    'privacy'     => ['label' => 'Política de Privacidad',     'icon' => '🔒', 'color' => 'purple', 'public_route' => 'public.legal.privacy'],
    'sms_consent' => ['label' => 'Consentimiento SMS',         'icon' => '📱', 'color' => 'green',  'public_route' => 'public.legal.sms'],
    'commercial'  => ['label' => 'Comunicaciones Comerciales', 'icon' => '📢', 'color' => 'orange', 'public_route' => null],
];
$colorMap = [
    'blue'   => ['bg' => 'bg-blue-50/60',   'badge' => 'bg-blue-100 text-blue-700',    'icon_bg' => 'bg-blue-100 text-blue-600'],
    'purple' => ['bg' => 'bg-purple-50/60', 'badge' => 'bg-purple-100 text-purple-700','icon_bg' => 'bg-purple-100 text-purple-600'],
    'green'  => ['bg' => 'bg-green-50/60',  'badge' => 'bg-green-100 text-green-700',  'icon_bg' => 'bg-green-100 text-green-600'],
    'orange' => ['bg' => 'bg-orange-50/60', 'badge' => 'bg-orange-100 text-orange-700','icon_bg' => 'bg-orange-100 text-orange-600'],
];
@endphp

<div class="space-y-5">
    @foreach($typeConfig as $type => $cfg)
    @php
        $docs = $documents->get($type, collect());
        $c    = $colorMap[$cfg['color']];
    @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 {{ $c['bg'] }}">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl {{ $c['icon_bg'] }} flex items-center justify-center text-base">{{ $cfg['icon'] }}</span>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $cfg['label'] }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $docs->count() }} versión(es)
                        @if($docs->where('is_active', true)->count())
                            · <span class="text-green-600 font-medium">1 vigente</span>
                        @else
                            · <span class="text-red-500">ninguna activa</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($cfg['public_route'])
                    <a href="{{ route($cfg['public_route']) }}" target="_blank"
                       class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 bg-white px-3 py-1.5 rounded-lg transition-colors">
                        Ver página pública →
                    </a>
                @endif
                <a href="{{ route('admin.legal-documents.create') }}?type={{ $type }}"
                   class="text-xs {{ $c['badge'] }} px-3 py-1.5 rounded-lg font-medium hover:opacity-80 transition-opacity">
                    + Nueva versión
                </a>
            </div>
        </div>

        @if($docs->isEmpty())
            <div class="px-5 py-10 text-center">
                <p class="text-gray-400 text-sm">No hay versiones creadas para este tipo.</p>
                <a href="{{ route('admin.legal-documents.create') }}?type={{ $type }}"
                   class="mt-2 inline-block text-xs text-blue-600 hover:underline">Crear primera versión →</a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="text-left px-5 py-3">Título</th>
                        <th class="text-left px-4 py-3">Versión</th>
                        <th class="text-left px-4 py-3">Estado</th>
                        <th class="text-left px-4 py-3">Aceptaciones</th>
                        <th class="text-left px-4 py-3">Publicado</th>
                        <th class="text-left px-4 py-3">Creado por</th>
                        <th class="text-right px-5 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($docs->sortByDesc('created_at') as $doc)
                    <tr class="hover:bg-gray-50/60 transition-colors {{ $doc->is_active ? 'bg-green-50/20' : '' }}">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.legal-documents.show', $doc) }}"
                               class="font-medium text-gray-800 hover:text-blue-600">
                                {{ $doc->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">v{{ $doc->version }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($doc->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Vigente
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $count = $acceptanceCounts->get($doc->id, 0); @endphp
                            @if($count > 0)
                                <a href="{{ route('admin.legal-documents.show', $doc) }}"
                                   class="text-xs text-purple-600 hover:text-purple-800 font-medium">
                                    {{ number_format($count) }} →
                                </a>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            {{ $doc->published_at ? $doc->published_at->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $doc->createdBy?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.legal-documents.show', $doc) }}"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">Ver</a>

                                @if(!$doc->is_active)
                                    <form method="POST" action="{{ route('admin.legal-documents.publish', $doc) }}">
                                        @csrf
                                        <button type="button"
                                                onclick="if(confirm('¿Publicar v{{ $doc->version }} como versión activa?')) this.closest('form').submit()"
                                                class="text-xs text-green-600 hover:text-green-800 font-medium">
                                            Publicar
                                        </button>
                                    </form>
                                    @if($acceptanceCounts->get($doc->id, 0) === 0)
                                    <form method="POST" action="{{ route('admin.legal-documents.destroy', $doc) }}">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                                onclick="if(confirm('¿Eliminar esta versión?')) this.closest('form').submit()"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium">
                                            Eliminar
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-300">Vigente</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @endforeach
</div>

@endsection
