@extends('layouts.admin')
@section('title', 'Landing Pages')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Landing Pages</h1>
        <p class="text-sm text-gray-500 mt-0.5">Páginas de autorización personalizadas para tus campañas SMS</p>
    </div>
    <a href="{{ route('admin.landing-configs.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva landing
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-5 text-sm">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-5 text-sm">
    {{ session('error') }}
</div>
@endif

@if($configs->isEmpty())
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <h3 class="text-base font-semibold text-gray-700 mb-2">Sin landing pages creadas</h3>
    <p class="text-sm text-gray-500 mb-5">Crea tu primera landing para personalizar la página de autorización de datos que ven tus clientes.</p>
    <a href="{{ route('admin.landing-configs.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
        Crear primera landing
    </a>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($configs as $config)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

        {{-- Preview thumbnail --}}
        <div class="h-36 relative flex items-center justify-center overflow-hidden"
             style="background: {{ $config->bg_color }};">
            @if($config->template === 'hero' && $config->hero_image_url)
                <img src="{{ $config->hero_image_url }}" class="absolute inset-0 w-full h-full object-cover opacity-40" alt="">
            @endif
            <div class="relative z-10 text-center px-4">
                @if($config->logo_url)
                    <img src="{{ $config->logo_url }}" class="h-8 mx-auto mb-2 object-contain" alt="logo">
                @endif
                <div class="w-32 h-3 rounded-full mx-auto mb-1.5 opacity-70"
                     style="background:{{ $config->brand_color }}"></div>
                <div class="w-20 h-2 bg-gray-300 rounded-full mx-auto mb-1 opacity-50"></div>
                <div class="w-24 h-2 bg-gray-300 rounded-full mx-auto opacity-40"></div>
                <div class="mt-2.5 w-28 h-6 rounded-lg mx-auto opacity-90"
                     style="background:{{ $config->brand_color }}"></div>
            </div>
        </div>

        {{-- Info --}}
        <div class="p-4 flex-1 flex flex-col">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <h3 class="text-sm font-semibold text-gray-900 leading-tight">{{ $config->name }}</h3>
                @if($config->is_default)
                <span class="text-[10px] bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full flex-shrink-0">
                    Por defecto
                </span>
                @endif
            </div>
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs text-gray-500 capitalize">
                    @php $labels = ['minimal' => 'Minimal', 'branded' => 'Branded', 'hero' => 'Hero']; @endphp
                    {{ $labels[$config->template] ?? $config->template }}
                </span>
                <span class="text-gray-200">·</span>
                <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                    <span class="w-3 h-3 rounded-full border border-gray-200 inline-block"
                          style="background:{{ $config->brand_color }}"></span>
                    {{ $config->brand_color }}
                </span>
                <span class="text-gray-200">·</span>
                <span class="text-xs text-gray-400">{{ $config->smsCampaigns()->count() }} campañas</span>
            </div>

            <div class="mt-auto flex items-center gap-2">
                <a href="{{ route('admin.landing-configs.preview', $config) }}"
                   target="_blank"
                   class="flex-1 text-center text-xs font-medium text-gray-600 hover:text-gray-800 bg-gray-50 hover:bg-gray-100 py-1.5 rounded-lg transition-colors">
                    Previsualizar
                </a>
                <a href="{{ route('admin.landing-configs.edit', $config) }}"
                   class="flex-1 text-center text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 py-1.5 rounded-lg transition-colors">
                    Editar
                </a>
                <form method="POST" action="{{ route('admin.landing-configs.destroy', $config) }}"
                      onsubmit="return confirm('¿Eliminar esta landing?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs font-medium text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 py-1.5 px-3 rounded-lg transition-colors">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
