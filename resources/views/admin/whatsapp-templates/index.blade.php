@extends('layouts.admin')
@section('title', 'Plantillas WhatsApp')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="{{ route('admin.whatsapp-campaigns.index') }}" class="hover:text-gray-600">Campañas WhatsApp</a>
            <span>/</span>
            <span class="text-gray-700 font-medium">Plantillas</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Plantillas WhatsApp</h1>
        <p class="text-sm text-gray-500 mt-0.5">Plantillas registradas en tu cuenta Zenvia y su estado de aprobación Meta</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.whatsapp-campaigns.index') }}"
           class="border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ← Campañas
        </a>
        @if($driver === 'zenvia')
            <a href="{{ route('admin.whatsapp-templates.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                + Nueva Plantilla
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif

@if($driver !== 'zenvia')
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
        <p class="text-yellow-800 font-semibold text-sm">Driver WhatsApp no es Zenvia</p>
        <p class="text-yellow-700 text-xs mt-1">Configura las credenciales en
            <a href="{{ route('admin.providers.index') }}" class="underline">Proveedores</a> para gestionar plantillas.</p>
    </div>
@elseif($templates->isEmpty())
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <div class="text-4xl mb-3">📋</div>
        <p class="text-gray-600 font-medium">No hay plantillas registradas</p>
        <p class="text-gray-400 text-sm mt-1">Crea tu primera plantilla para usarla en campañas masivas.</p>
        <a href="{{ route('admin.whatsapp-templates.create') }}"
           class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-semibold">
            + Nueva Plantilla
        </a>
    </div>
@else
    {{-- Legend --}}
    <div class="flex flex-wrap gap-3 mb-4 text-xs">
        @foreach(['APPROVED'=>['bg-green-100 text-green-800','Aprobada'],'PENDING'=>['bg-yellow-100 text-yellow-800','Pendiente'],'REJECTED'=>['bg-red-100 text-red-800','Rechazada'],'PAUSED'=>['bg-gray-100 text-gray-600','Pausada']] as $s=>[$cls,$lbl])
            <span class="flex items-center gap-1"><span class="px-2 py-0.5 rounded-full {{ $cls }}">{{ $lbl }}</span></span>
        @endforeach
    </div>

    <div class="grid gap-4">
        @foreach($templates as $tpl)
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-start justify-between gap-4">
                {{-- Left: info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="font-semibold text-gray-900 font-mono text-sm">{{ $tpl['name'] }}</span>
                        @php
                            $statusClass = match($tpl['status']) {
                                'APPROVED' => 'bg-green-100 text-green-800',
                                'PENDING'  => 'bg-yellow-100 text-yellow-800',
                                'REJECTED' => 'bg-red-100 text-red-800',
                                default    => 'bg-gray-100 text-gray-600',
                            };
                            $statusLabel = match($tpl['status']) {
                                'APPROVED' => '✓ Aprobada',
                                'PENDING'  => '⏳ Pendiente',
                                'REJECTED' => '✗ Rechazada',
                                default    => $tpl['status'],
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        @if($tpl['category'])
                            <span class="px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700">{{ $tpl['category'] }}</span>
                        @endif
                        @if($tpl['locale'])
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">🌐 {{ $tpl['locale'] }}</span>
                        @endif
                    </div>

                    {{-- ID --}}
                    <p class="text-[10px] text-gray-400 font-mono mb-2">{{ $tpl['id'] }}</p>

                    {{-- Header --}}
                    @if($tpl['header'])
                        <div class="mb-1">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mr-1">Header</span>
                            <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $tpl['header']['format'] }}</span>
                            @if($tpl['header']['text'])
                                <span class="text-xs text-gray-600 ml-1">{{ $tpl['header']['text'] }}</span>
                            @endif
                        </div>
                    @endif

                    {{-- Body --}}
                    @if($tpl['body'])
                        <div class="mb-1">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mr-1">Body</span>
                            <span class="text-sm text-gray-800 leading-relaxed">{{ Str::limit($tpl['body'], 120) }}</span>
                        </div>
                    @endif

                    {{-- Variables --}}
                    @if(!empty($tpl['variables']))
                        <div class="flex gap-1 flex-wrap mt-1">
                            @foreach($tpl['variables'] as $v)
                                <code class="text-[10px] bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100">@{{{{ $v }}}}</code>
                            @endforeach
                        </div>
                    @endif

                    {{-- Footer --}}
                    @if($tpl['footer'])
                        <p class="text-xs text-gray-400 mt-1">Footer: {{ $tpl['footer'] }}</p>
                    @endif

                    {{-- Buttons --}}
                    @if(!empty($tpl['buttons']))
                        <div class="flex gap-1 flex-wrap mt-2">
                            @foreach($tpl['buttons'] as $btn)
                                <span class="text-xs border border-gray-200 rounded px-2 py-0.5 text-gray-600">
                                    @if(($btn['type'] ?? '') === 'QUICK_REPLY') 💬
                                    @elseif(($btn['type'] ?? '') === 'URL') 🔗
                                    @elseif(($btn['type'] ?? '') === 'PHONE_NUMBER') 📞
                                    @endif
                                    {{ $btn['text'] ?? '' }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($tpl['status'] === 'APPROVED')
                        <a href="{{ route('admin.whatsapp-campaigns.create') }}"
                           class="text-xs bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 px-3 py-1.5 rounded-lg transition-colors font-medium">
                            Usar en campaña
                        </a>
                    @endif
                    <form method="POST" action="{{ route('admin.whatsapp-templates.destroy', $tpl['id']) }}"
                          onsubmit="return confirm('¿Eliminar la plantilla {{ addslashes($tpl['name']) }}? Esta acción no se puede deshacer.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-400 hover:text-red-700 border border-red-100 hover:border-red-300 px-3 py-1.5 rounded-lg transition-colors">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <p class="text-xs text-gray-400 mt-4 text-center">
        {{ $templates->count() }} plantilla(s) — Los estados se actualizan desde Meta/WhatsApp (aprobación: 24-48h normalmente).
        <a href="{{ route('admin.whatsapp-templates.index') }}" class="underline ml-1">Actualizar</a>
    </p>
@endif

@endsection
