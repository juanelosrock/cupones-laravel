<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview — {{ $landingConfig->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        .ql-content p { margin-bottom: 0.5rem; }
        .ql-content ul { list-style: disc; padding-left: 1.25rem; margin-bottom: 0.5rem; }
        .ql-content ol { list-style: decimal; padding-left: 1.25rem; margin-bottom: 0.5rem; }
        .ql-content a { color: #2563eb; text-decoration: underline; }
    </style>
</head>
<body>

{{-- Preview banner --}}
<div class="fixed top-0 left-0 right-0 z-50 bg-amber-500 text-amber-900 text-xs font-semibold text-center py-1.5 flex items-center justify-center gap-3">
    <span>PREVISUALIZACIÓN — {{ $landingConfig->name }}</span>
    <a href="{{ route('admin.landing-configs.edit', $landingConfig) }}"
       class="underline hover:text-amber-950">Editar</a>
    <span>·</span>
    <button onclick="window.close()" class="underline hover:text-amber-950">Cerrar</button>
</div>

{{-- =====================================================================
     TEMPLATE: MINIMAL
     ===================================================================== --}}
@if($landingConfig->template === 'minimal')
<div class="min-h-screen flex flex-col items-center justify-start pt-16 pb-12 px-4"
     style="background: {{ $landingConfig->bg_color }};">

    {{-- Logo --}}
    <div class="w-full max-w-md mb-6 text-center">
        @if($landingConfig->logo_url)
            <img src="{{ $landingConfig->logo_url }}" class="h-12 mx-auto object-contain mb-2" alt="logo">
        @else
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                     style="background:{{ $landingConfig->brand_color }}">
                    <span class="text-white font-bold text-sm">C</span>
                </div>
                <span class="font-bold text-gray-800 text-lg">CuponesHub</span>
            </div>
        @endif
        <div class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full mt-1"
             style="background:{{ $landingConfig->brand_color }}22; color:{{ $landingConfig->brand_color }}">
            <span>🎁</span>
            <span>25% de descuento te espera</span>
        </div>
    </div>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $landingConfig->heading }}</h1>
        @if($landingConfig->subheading)
            <p class="text-sm text-gray-500 mb-4">{{ $landingConfig->subheading }}</p>
        @endif

        @if($landingConfig->body_html)
        <div class="mb-4 text-sm text-gray-600 ql-content">{!! $landingConfig->body_html !!}</div>
        @endif

        <div class="mb-4 bg-gray-50 border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Consentimiento SMS — v1.0</p>
            <p class="text-xs text-gray-500">Al aceptar, autorizas el tratamiento de tus datos personales según la Ley 1581 de 2012 (Colombia)...</p>
        </div>

        <label class="flex items-start gap-3 mb-4 cursor-pointer">
            <input type="checkbox" checked class="mt-0.5 h-5 w-5 rounded border-gray-300 flex-shrink-0"
                   style="accent-color: {{ $landingConfig->brand_color }}">
            <span class="text-sm text-gray-700">Acepto el <strong>tratamiento de mis datos personales</strong> para recibir comunicaciones comerciales, de acuerdo con la Ley 1581 de 2012.</span>
        </label>
        <label class="flex items-start gap-3 mb-6 cursor-pointer">
            <input type="checkbox" checked class="mt-0.5 h-5 w-5 rounded border-gray-300 flex-shrink-0"
                   style="accent-color: {{ $landingConfig->brand_color }}">
            <span class="text-sm text-gray-700">He leído y acepto los <a href="#" class="font-medium underline" style="color:{{ $landingConfig->brand_color }}">Términos y Condiciones</a>.</span>
        </label>

        <button class="w-full text-white font-semibold py-3.5 rounded-xl text-base transition-colors"
                style="background:{{ $landingConfig->brand_color }}">
            {{ $landingConfig->button_text }}
        </button>
        <p class="text-xs text-gray-400 text-center mt-4">Tu aceptación quedará registrada con fecha, hora e IP.</p>
    </div>

    @include('admin.landing-configs._preview_footer', ['config' => $landingConfig])
</div>

{{-- =====================================================================
     TEMPLATE: BRANDED
     ===================================================================== --}}
@elseif($landingConfig->template === 'branded')
<div class="min-h-screen flex flex-col"
     style="background: {{ $landingConfig->bg_color }};">

    {{-- Branded header --}}
    <div class="py-6 px-4 flex items-center justify-center shadow-sm"
         style="background:{{ $landingConfig->brand_color }}">
        @if($landingConfig->logo_url)
            <img src="{{ $landingConfig->logo_url }}" class="h-10 object-contain" alt="logo"
                 style="filter: brightness(0) invert(1);">
        @else
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">C</span>
                </div>
                <span class="font-bold text-white text-lg">CuponesHub</span>
            </div>
        @endif
    </div>

    {{-- Discount badge --}}
    <div class="text-center -mt-1 pb-1 pt-3">
        <span class="inline-flex items-center gap-1.5 bg-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm border"
              style="color:{{ $landingConfig->brand_color }}; border-color:{{ $landingConfig->brand_color }}44">
            🎁 25% de descuento te espera
        </span>
    </div>

    <div class="flex-1 flex items-start justify-center px-4 pt-5 pb-12">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $landingConfig->heading }}</h1>
            @if($landingConfig->subheading)
                <p class="text-sm text-gray-500 mb-4">{{ $landingConfig->subheading }}</p>
            @endif

            @if($landingConfig->body_html)
            <div class="mb-4 text-sm text-gray-600 ql-content">{!! $landingConfig->body_html !!}</div>
            @endif

            <div class="mb-4 bg-gray-50 border border-gray-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Consentimiento SMS — v1.0</p>
                <p class="text-xs text-gray-500">Al aceptar, autorizas el tratamiento de tus datos personales...</p>
            </div>

            <label class="flex items-start gap-3 mb-4 cursor-pointer">
                <input type="checkbox" checked class="mt-0.5 h-5 w-5 rounded border-gray-300 flex-shrink-0"
                       style="accent-color: {{ $landingConfig->brand_color }}">
                <span class="text-sm text-gray-700">Acepto el <strong>tratamiento de mis datos personales</strong>.</span>
            </label>
            <label class="flex items-start gap-3 mb-6 cursor-pointer">
                <input type="checkbox" checked class="mt-0.5 h-5 w-5 rounded border-gray-300 flex-shrink-0"
                       style="accent-color: {{ $landingConfig->brand_color }}">
                <span class="text-sm text-gray-700">He leído y acepto los <a href="#" class="font-medium underline" style="color:{{ $landingConfig->brand_color }}">Términos y Condiciones</a>.</span>
            </label>

            <button class="w-full text-white font-semibold py-3.5 rounded-xl text-base"
                    style="background:{{ $landingConfig->brand_color }}">
                {{ $landingConfig->button_text }}
            </button>
            <p class="text-xs text-gray-400 text-center mt-4">Tu aceptación quedará registrada con fecha, hora e IP.</p>
        </div>
    </div>

    @include('admin.landing-configs._preview_footer', ['config' => $landingConfig])
</div>

{{-- =====================================================================
     TEMPLATE: HERO
     ===================================================================== --}}
@elseif($landingConfig->template === 'hero')
@php
    $previewHeroBg = $landingConfig->hero_image_url
        ? "background-image:url('" . e($landingConfig->hero_image_url) . "');background-size:cover;background-position:center;background-repeat:no-repeat;"
        : "background:linear-gradient(135deg,{$landingConfig->brand_color} 0%,#0f172a 100%);";
@endphp
<div class="min-h-screen flex flex-col items-center justify-start pt-16 pb-12 px-4"
     style="position:relative;overflow:hidden;{{ $previewHeroBg }}">

    {{-- Dark overlay --}}
    @if($landingConfig->hero_image_url)
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.50);z-index:0;"></div>
    @endif

    {{-- Wrapper sobre el overlay --}}
    <div style="position:relative;z-index:1;width:100%;display:flex;flex-direction:column;align-items:center;">

    {{-- Logo --}}
    <div class="w-full max-w-md mb-6 text-center">
        @if($landingConfig->logo_url)
            <img src="{{ $landingConfig->logo_url }}" class="h-12 mx-auto object-contain" alt="logo"
                 style="filter: brightness(0) invert(1) drop-shadow(0 2px 4px rgba(0,0,0,0.5))">
        @else
            <div class="inline-flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <span class="text-white font-bold">C</span>
                </div>
                <span class="font-bold text-white text-xl drop-shadow">CuponesHub</span>
            </div>
        @endif
        <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1 rounded-full mt-2">
            🎁 25% de descuento te espera
        </div>
    </div>

    {{-- Glassmorphism card --}}
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl p-6">
        <h1 class="text-xl font-bold text-white mb-1">{{ $landingConfig->heading }}</h1>
        @if($landingConfig->subheading)
            <p class="text-sm text-white/80 mb-4">{{ $landingConfig->subheading }}</p>
        @endif

        @if($landingConfig->body_html)
        <div class="mb-4 text-sm text-white/90 ql-content">{!! $landingConfig->body_html !!}</div>
        @endif

        <div class="mb-4 bg-white/10 border border-white/20 rounded-xl p-4">
            <p class="text-xs font-semibold text-white/70 mb-1 uppercase tracking-wide">Consentimiento SMS — v1.0</p>
            <p class="text-xs text-white/60">Al aceptar, autorizas el tratamiento de tus datos personales...</p>
        </div>

        <label class="flex items-start gap-3 mb-4 cursor-pointer">
            <input type="checkbox" checked class="mt-0.5 h-5 w-5 rounded border-white/30 bg-white/10 flex-shrink-0">
            <span class="text-sm text-white/90">Acepto el <strong>tratamiento de mis datos personales</strong>.</span>
        </label>
        <label class="flex items-start gap-3 mb-6 cursor-pointer">
            <input type="checkbox" checked class="mt-0.5 h-5 w-5 rounded border-white/30 bg-white/10 flex-shrink-0">
            <span class="text-sm text-white/90">He leído y acepto los <a href="#" class="font-semibold underline text-white">Términos y Condiciones</a>.</span>
        </label>

        <button class="w-full font-bold py-3.5 rounded-xl text-base shadow-lg"
                style="background:{{ $landingConfig->brand_color }}; color:white">
            {{ $landingConfig->button_text }}
        </button>
        <p class="text-xs text-white/40 text-center mt-4">Tu aceptación quedará registrada con fecha, hora e IP.</p>
    </div>

    @include('admin.landing-configs._preview_footer', ['config' => $landingConfig, 'dark' => true])

    </div>{{-- /z-index wrapper --}}
</div>

{{-- =====================================================================
     TEMPLATE: PROMO
     ===================================================================== --}}
@elseif($landingConfig->template === 'promo')
@php
    $prevDiscount = $landingConfig->couponBatch?->discount_type === 'percentage'
        ? $landingConfig->couponBatch->discount_value . '%'
        : null;
    $prevLabel    = $prevDiscount ? 'OFF' : null;
    // fallback demo values for preview
    if (!$prevDiscount) { $prevDiscount = '50%'; $prevLabel = 'OFF'; }
@endphp
<div class="min-h-screen flex flex-col items-center justify-start py-10 px-4"
     style="background: {{ $landingConfig->bg_color }}; font-family: system-ui,-apple-system,sans-serif;">

    {{-- Logo --}}
    <div class="w-full max-w-sm mb-5 text-center">
        @if($landingConfig->logo_url)
            <img src="{{ $landingConfig->logo_url }}" class="h-14 mx-auto object-contain" alt="logo">
        @else
            <div class="inline-flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                     style="background:{{ $landingConfig->brand_color }}">
                    <span class="text-white font-bold text-sm">C</span>
                </div>
                <span class="font-bold text-gray-800 text-lg">CuponesHub</span>
            </div>
        @endif
    </div>

    {{-- Discount hero --}}
    <div class="w-full max-w-sm text-center mb-3">
        <div class="leading-none font-black tracking-tighter"
             style="font-size:5rem; color:{{ $landingConfig->brand_color }}; text-shadow:2px 2px 0 {{ $landingConfig->brand_color }}33">
            {{ $prevDiscount }}<span style="font-size:2.5rem">{{ $prevLabel }}</span>
        </div>
        @if($landingConfig->heading)
        <div class="inline-block bg-gray-900 text-white font-extrabold uppercase tracking-widest px-6 py-2 rounded-lg mt-1 text-lg">
            {{ $landingConfig->heading }}
        </div>
        @endif
    </div>

    {{-- Form card --}}
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @if($landingConfig->subheading)
        <p class="text-base font-bold text-gray-800 mb-4">{{ $landingConfig->subheading }}</p>
        @else
        <p class="text-base font-bold text-gray-800 mb-4">Regístrate</p>
        @endif

        @if($landingConfig->body_html)
        <div class="mb-4 text-sm text-gray-600 ql-content">{!! $landingConfig->body_html !!}</div>
        @endif

        <div class="space-y-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">E-Mail</label>
                <input type="email" placeholder="correo@ejemplo.com" disabled
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono</label>
                <input type="tel" placeholder="3001234567" disabled
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50">
            </div>
        </div>

        <label class="flex items-start gap-2 mb-5 cursor-pointer">
            <input type="checkbox" disabled checked
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 flex-shrink-0"
                   style="accent-color: {{ $landingConfig->brand_color }}">
            <span class="text-xs text-gray-500">Acepto el tratamiento de mis datos personales, términos y condiciones y el envío de comunicaciones SMS.</span>
        </label>

        <button class="w-full text-white font-bold py-3.5 rounded-xl text-base"
                style="background:{{ $landingConfig->brand_color }}">
            {{ $landingConfig->button_text }}
        </button>
        <p class="text-xs text-gray-400 text-center mt-3">Tu aceptación quedará registrada con fecha, hora e IP.</p>
    </div>

    @include('admin.landing-configs._preview_footer', ['config' => $landingConfig])
</div>
@endif

</body>
</html>
