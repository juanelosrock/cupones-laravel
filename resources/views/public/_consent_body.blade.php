@php $isHero = $heroMode ?? false; @endphp

@if(!isset($accepted) || !$accepted)
{{-- ── Consent form ── --}}
<div class="w-full max-w-md {{ $isHero ? 'bg-white/10 backdrop-blur-md border border-white/20 text-white' : 'bg-white border border-gray-100' }} rounded-2xl shadow-sm p-6">

    <h1 class="text-xl font-bold {{ $isHero ? 'text-white' : 'text-gray-900' }} mb-1">{{ $heading }}</h1>
    @if($subheading)
        <p class="text-sm {{ $isHero ? 'text-white/75' : 'text-gray-500' }} mb-4">
            @if($customerName) Hola <strong>{{ $customerName }}</strong>, @endif{{ $subheading }}
        </p>
    @elseif($customerName)
        <p class="text-sm {{ $isHero ? 'text-white/75' : 'text-gray-500' }} mb-4">
            Hola <strong>{{ $customerName }}</strong>, para revelarte tu código necesitamos tu autorización.
        </p>
    @endif

    @if($errors->any())
        <div class="mb-4 {{ $isHero ? 'bg-red-500/30 border-red-400/50' : 'bg-red-50 border-red-200' }} border rounded-xl p-4">
            <ul class="text-sm {{ $isHero ? 'text-white' : 'text-red-700' }} space-y-1">
                @foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if($bodyHtml)
    <div class="mb-4 text-sm {{ $isHero ? 'text-white/85' : 'text-gray-600' }} ql-content">{!! $bodyHtml !!}</div>
    @endif

    {{-- Legal doc preview --}}
    @if($legalDoc)
        <div class="mb-5 {{ $isHero ? 'bg-white/10 border-white/20' : 'bg-gray-50 border-gray-200' }} border rounded-xl p-4 max-h-48 overflow-y-auto">
            <p class="text-xs font-semibold {{ $isHero ? 'text-white/70' : 'text-gray-600' }} mb-2 uppercase tracking-wide">
                {{ $legalDoc->title }} — v{{ $legalDoc->version }}
            </p>
            <div class="text-xs {{ $isHero ? 'text-white/60' : 'text-gray-600' }} leading-relaxed whitespace-pre-line">
                {{ Str::limit($legalDoc->content, 600) }}
            </div>
            @if(strlen($legalDoc->content) > 600)
                <a href="{{ route('public.legal.sms') }}" target="_blank"
                   class="inline-block mt-2 text-xs underline {{ $isHero ? 'text-white/80' : 'text-blue-600' }}">
                    Leer documento completo →
                </a>
            @endif
        </div>
    @else
        <div class="mb-5 {{ $isHero ? 'bg-white/10 border-white/20' : 'bg-gray-50 border-gray-200' }} border rounded-xl p-4">
            <p class="text-xs {{ $isHero ? 'text-white/60' : 'text-gray-500' }}">
                Al aceptar, autorizas el tratamiento de tus datos personales según la
                <a href="{{ route('public.legal.privacy') }}" target="_blank" class="underline {{ $isHero ? 'text-white' : 'text-blue-600' }}">Política de Privacidad</a>
                y los
                <a href="{{ route('public.legal.terms') }}" target="_blank" class="underline {{ $isHero ? 'text-white' : 'text-blue-600' }}">Términos y Condiciones</a>.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('public.consent.accept', $recipient->consent_token) }}">
        @csrf
        <label class="flex items-start gap-3 mb-4 cursor-pointer">
            <input type="checkbox" name="accept_data" value="1"
                   class="mt-0.5 h-5 w-5 rounded border-gray-300 flex-shrink-0 @error('accept_data') border-red-400 @enderror"
                   style="accent-color: {{ $brandColor }}"
                   {{ old('accept_data') ? 'checked' : '' }}>
            <span class="text-sm {{ $isHero ? 'text-white/85' : 'text-gray-700' }}">
                Acepto el <strong>tratamiento de mis datos personales</strong> para recibir comunicaciones y descuentos personalizados, de acuerdo con la Ley 1581 de 2012.
            </span>
        </label>
        <label class="flex items-start gap-3 mb-6 cursor-pointer">
            <input type="checkbox" name="accept_terms" value="1"
                   class="mt-0.5 h-5 w-5 rounded border-gray-300 flex-shrink-0 @error('accept_terms') border-red-400 @enderror"
                   style="accent-color: {{ $brandColor }}"
                   {{ old('accept_terms') ? 'checked' : '' }}>
            <span class="text-sm {{ $isHero ? 'text-white/85' : 'text-gray-700' }}">
                He leído y acepto los
                <a href="{{ route('public.legal.terms') }}" target="_blank" class="font-medium underline"
                   style="{{ $isHero ? 'color:white' : 'color:'.$brandColor }}">Términos y Condiciones</a>
                y la
                <a href="{{ route('public.legal.privacy') }}" target="_blank" class="font-medium underline"
                   style="{{ $isHero ? 'color:white' : 'color:'.$brandColor }}">Política de Privacidad</a>.
            </span>
        </label>

        <button type="submit"
                class="w-full font-semibold py-3.5 rounded-xl text-base shadow transition-opacity hover:opacity-90 active:opacity-75"
                style="background:{{ $brandColor }}; color:white">
            {{ $btnText }}
        </button>
    </form>
    <p class="text-xs {{ $isHero ? 'text-white/40' : 'text-gray-400' }} text-center mt-4">
        Tu aceptación quedará registrada con fecha, hora e IP para cumplimiento legal.
    </p>
</div>

@else
{{-- ── Success screen ── --}}
<div class="w-full max-w-md space-y-4">

    <div class="{{ $isHero ? 'bg-white/15 border-white/25 backdrop-blur-md' : 'bg-green-50 border-green-200' }} border rounded-2xl p-5 text-center">
        <div class="text-4xl mb-2">✅</div>
        <h2 class="text-lg font-bold {{ $isHero ? 'text-white' : 'text-green-800' }} mb-1">{{ $okHeading }}</h2>
        <p class="text-sm {{ $isHero ? 'text-white/75' : 'text-green-700' }}">
            {{ $customerName ? 'Gracias ' . $customerName . ', ' : '' }}{{ $okText }}
        </p>
    </div>

    @if($recipient->assigned_coupon_code)
    <div class="{{ $isHero ? 'bg-white/10 border-white/20 backdrop-blur-md' : 'bg-white border-gray-100' }} border rounded-2xl shadow-sm p-6 text-center">
        <p class="text-xs font-semibold {{ $isHero ? 'text-white/60' : 'text-gray-500' }} uppercase tracking-widest mb-3">
            Tu código de descuento
        </p>

        @if($batch)
        <div class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full mb-4"
             style="background:{{ $brandColor }}33; color:{{ $isHero ? 'white' : $brandColor }}">
            🎁
            {{ $batch->discount_type === 'percentage'
                ? $batch->discount_value . '% de descuento'
                : '$ ' . number_format($batch->discount_value, 0, ',', '.') . ' de descuento' }}
        </div>
        @endif

        <div class="{{ $isHero ? 'bg-white/10 border-white/20' : 'bg-gray-50 border-gray-300' }} border-2 border-dashed rounded-xl p-4 mb-4 cursor-pointer select-all"
             onclick="copyCode(this)" title="Toca para copiar">
            <p class="text-3xl font-mono font-bold tracking-widest {{ $isHero ? 'text-white' : 'text-gray-900' }}">
                {{ $recipient->assigned_coupon_code }}
            </p>
        </div>

        <p class="text-xs {{ $isHero ? 'text-white/50' : 'text-gray-500' }} mb-4">Toca el código para copiarlo</p>

        @if($batch)
        <div class="{{ $isHero ? 'bg-white/10' : 'bg-blue-50' }} rounded-xl p-3 text-left space-y-1.5">
            @if($batch->min_purchase_amount)
            <p class="text-xs {{ $isHero ? 'text-white/70' : 'text-blue-700' }}">
                <span class="font-medium">Compra mínima:</span>
                $ {{ number_format($batch->min_purchase_amount, 0, ',', '.') }}
            </p>
            @endif
            @if($batch->end_date)
            <p class="text-xs {{ $isHero ? 'text-white/70' : 'text-blue-700' }}">
                <span class="font-medium">Válido hasta:</span>
                {{ \Carbon\Carbon::parse($batch->end_date)->format('d/m/Y') }}
            </p>
            @endif
        </div>
        @endif

        <button onclick="copyCode(document.querySelector('.font-mono.font-bold'))"
                class="mt-4 w-full font-semibold py-3 rounded-xl text-sm"
                style="background:{{ $brandColor }}; color:white">
            Copiar código
        </button>
    </div>
    @else
    <div class="{{ $isHero ? 'bg-white/10 border-white/20 backdrop-blur-md' : 'bg-white border-gray-100' }} border rounded-2xl shadow-sm p-6 text-center">
        <p class="text-4xl mb-3">🎟️</p>
        <h3 class="text-base font-semibold {{ $isHero ? 'text-white' : 'text-gray-800' }} mb-2">¡Autorización aceptada!</h3>
        <p class="text-sm {{ $isHero ? 'text-white/60' : 'text-gray-500' }}">
            No hay un código asignado a esta campaña, pero tu autorización quedó registrada correctamente.
        </p>
    </div>
    @endif

    <p class="text-xs {{ $isHero ? 'text-white/35' : 'text-gray-400' }} text-center">
        Aceptado el {{ $recipient->consent_accepted_at?->format('d/m/Y \a \l\a\s H:i') }} · IP: {{ $recipient->acceptance_ip }}
    </p>
</div>
@endif
