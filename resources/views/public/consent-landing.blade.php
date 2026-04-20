@php
    $lc = $recipient->campaign?->landingConfig
        ?? \App\Models\LandingPageConfig::getDefault();

    // Defaults if no config at all
    $tpl          = $lc?->template         ?? 'minimal';
    $brandColor   = $lc?->brand_color      ?? '#2563eb';
    $bgColor      = $lc?->bg_color         ?? '#f1f5f9';
    $logoUrl      = $lc?->logo_url         ?? null;
    $heroUrl      = $lc?->hero_image_url   ?? null;
    $heading      = $lc?->heading          ?? 'Autorización de datos personales';
    $subheading   = $lc?->subheading       ?? null;
    $bodyHtml     = $lc?->body_html        ?? null;
    $btnText      = $lc?->button_text      ?? 'Aceptar y ver mi código';
    $okHeading    = $lc?->success_heading  ?? '¡Autorización registrada!';
    $okText       = $lc?->success_text     ?? 'Tu consentimiento fue guardado correctamente.';
    $footerText   = $lc?->footer_text      ?? null;

    $batch        = $recipient->campaign?->couponBatch;
    $customerName = $recipient->customer?->name;

    $discountBadge = null;
    if ($batch) {
        $discountBadge = $batch->discount_type === 'percentage'
            ? $batch->discount_value . '% de descuento'
            : '$ ' . number_format($batch->discount_value, 0, ',', '.') . ' de descuento';
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }} — CuponesHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .ql-content p { margin-bottom: 0.5rem; }
        .ql-content ul { list-style: disc; padding-left: 1.25rem; margin-bottom: 0.5rem; }
        .ql-content ol { list-style: decimal; padding-left: 1.25rem; margin-bottom: 0.5rem; }
        .ql-content a { text-decoration: underline; }
    </style>
</head>
<body>

{{-- =====================================================================
     TEMPLATE: MINIMAL
     ===================================================================== --}}
@if($tpl === 'minimal')
<div class="min-h-screen flex flex-col items-center justify-start py-10 px-4"
     style="background: {{ $bgColor }}; font-family: system-ui, -apple-system, sans-serif;">

    {{-- Logo --}}
    <div class="w-full max-w-md mb-6 text-center">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="h-12 mx-auto object-contain mb-2" alt="logo">
        @else
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                     style="background:{{ $brandColor }}">
                    <span class="text-white font-bold text-sm">C</span>
                </div>
                <span class="font-bold text-gray-800 text-lg">CuponesHub</span>
            </div>
        @endif
        @if($discountBadge)
        <div class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full mt-1"
             style="background:{{ $brandColor }}22; color:{{ $brandColor }}">
            🎁 {{ $discountBadge }} te espera
        </div>
        @endif
    </div>

    @include('public._consent_body', compact('accepted','recipient','heading','subheading','bodyHtml','btnText','okHeading','okText','batch','customerName','brandColor','legalDoc','discountBadge'))

    @include('public._consent_footer', compact('footerText','brandColor'))
</div>

{{-- =====================================================================
     TEMPLATE: BRANDED
     ===================================================================== --}}
@elseif($tpl === 'branded')
<div class="min-h-screen flex flex-col"
     style="background: {{ $bgColor }}; font-family: system-ui, -apple-system, sans-serif;">

    {{-- Branded header --}}
    <div class="py-5 px-4 flex items-center justify-center shadow-sm"
         style="background:{{ $brandColor }}">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="h-10 object-contain"
                 style="filter: brightness(0) invert(1)" alt="logo">
        @else
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">C</span>
                </div>
                <span class="font-bold text-white text-lg">CuponesHub</span>
            </div>
        @endif
    </div>

    @if($discountBadge)
    <div class="text-center pt-3">
        <span class="inline-flex items-center gap-1.5 bg-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm border"
              style="color:{{ $brandColor }}; border-color:{{ $brandColor }}44">
            🎁 {{ $discountBadge }} te espera
        </span>
    </div>
    @endif

    <div class="flex-1 flex items-start justify-center px-4 pt-5 pb-12">
        @include('public._consent_body', compact('accepted','recipient','heading','subheading','bodyHtml','btnText','okHeading','okText','batch','customerName','brandColor','legalDoc','discountBadge'))
    </div>

    @include('public._consent_footer', compact('footerText','brandColor'))
</div>

{{-- =====================================================================
     TEMPLATE: HERO
     ===================================================================== --}}
@elseif($tpl === 'hero')
@php
    $heroBg = $heroUrl
        ? "background-image:url('" . e($heroUrl) . "');background-size:cover;background-position:center;background-repeat:no-repeat;"
        : "background:linear-gradient(135deg,{$brandColor} 0%,#0f172a 100%);";
@endphp
<div class="min-h-screen flex flex-col items-center justify-start pt-10 pb-12 px-4"
     style="position:relative;overflow:hidden;font-family:system-ui,-apple-system,sans-serif;{{ $heroBg }}">

    {{-- Dark overlay (solo cuando hay imagen) --}}
    @if($heroUrl)
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.55);z-index:0;"></div>
    @endif

    {{-- Contenido por encima del overlay --}}
    <div style="position:relative;z-index:1;width:100%;display:flex;flex-direction:column;align-items:center;">

        {{-- Logo --}}
        <div class="w-full max-w-md mb-6 text-center">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" class="h-12 mx-auto object-contain"
                     style="filter:brightness(0) invert(1) drop-shadow(0 2px 6px rgba(0,0,0,.5))" alt="logo">
            @else
                <div class="inline-flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                         style="background:rgba(255,255,255,.2);backdrop-filter:blur(8px)">
                        <span style="color:#fff;font-weight:700;">C</span>
                    </div>
                    <span style="color:#fff;font-weight:700;font-size:1.25rem;text-shadow:0 1px 4px rgba(0,0,0,.4)">CuponesHub</span>
                </div>
            @endif
            @if($discountBadge)
            <div class="inline-flex items-center gap-1.5 text-white text-xs font-semibold px-3 py-1 rounded-full mt-2"
                 style="background:rgba(255,255,255,.2);backdrop-filter:blur(8px)">
                🎁 {{ $discountBadge }} te espera
            </div>
            @endif
        </div>

        @include('public._consent_body', compact('accepted','recipient','heading','subheading','bodyHtml','btnText','okHeading','okText','batch','customerName','brandColor','legalDoc','discountBadge'), ['heroMode' => true])

        @include('public._consent_footer', compact('footerText','brandColor'), ['dark' => true])

    </div>
</div>

{{-- =====================================================================
     TEMPLATE: PROMO
     ===================================================================== --}}
@elseif($tpl === 'promo')
@php
    if ($batch && $batch->discount_type === 'percentage') {
        $promoNum    = rtrim(rtrim(number_format($batch->discount_value, 2, '.', ''), '0'), '.') . '%';
        $promoSuffix = 'OFF';
    } elseif ($batch) {
        $promoNum    = '$ ' . number_format($batch->discount_value, 0, ',', '.');
        $promoSuffix = 'DE DESC.';
    } else {
        $promoNum    = null;
        $promoSuffix = null;
    }
@endphp
<div class="min-h-screen flex flex-col items-center justify-start py-10 px-4"
     style="background:{{ $bgColor }};font-family:system-ui,-apple-system,sans-serif;">

    {{-- Card superior: logo + descuento --}}
    <div style="width:100%;max-width:400px;background:white;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.1);border:1px solid #e5e7eb;padding:24px 24px 20px;text-align:center;margin-bottom:12px;">

        {{-- Logo --}}
        @if($logoUrl)
            <img src="{{ $logoUrl }}" style="height:72px;max-width:100%;object-fit:contain;display:block;margin:0 auto 16px;" alt="logo">
        @else
            <div style="display:inline-flex;align-items:center;gap:8px;margin-bottom:16px;">
                <div style="width:36px;height:36px;border-radius:8px;background:{{ $brandColor }};display:flex;align-items:center;justify-content:center;">
                    <span style="color:white;font-weight:700;font-size:1rem;">C</span>
                </div>
                <span style="font-weight:700;color:#1f2937;font-size:1.25rem;">CuponesHub</span>
            </div>
        @endif

        {{-- Discount display --}}
        @if($promoNum)
        <div style="display:flex;align-items:baseline;justify-content:center;line-height:1;color:{{ $brandColor }};">
            <span style="font-size:5.5rem;font-weight:900;letter-spacing:-3px;">{{ $promoNum }}</span><span style="font-size:3.8rem;font-weight:900;letter-spacing:-1px;">{{ $promoSuffix }}</span>
        </div>
        @endif

        {{-- Heading badge (e.g. DOMICILIOS) --}}
        @if($heading)
        <div style="display:inline-block;background:#111827;color:white;font-weight:800;font-size:1.1rem;letter-spacing:.1em;text-transform:uppercase;padding:7px 24px;border-radius:8px;margin-top:10px;">
            {{ $heading }}
        </div>
        @endif

    </div>

    @if(!$accepted)
    {{-- ── Form ── --}}
    <div style="width:100%;max-width:400px;background:white;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #f3f4f6;padding:24px;">

        <p style="font-weight:700;font-size:1rem;color:#111827;margin-bottom:16px;">
            {{ $subheading ?: 'Regístrate' }}
        </p>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px;margin-bottom:16px;">
            @foreach($errors->all() as $error)
            <p style="font-size:0.8rem;color:#dc2626;">• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        @if($bodyHtml)
        <div style="font-size:0.875rem;color:#4b5563;margin-bottom:16px;" class="ql-content">{!! $bodyHtml !!}</div>
        @endif

        <form method="POST" action="{{ route('public.consent.accept', $recipient->consent_token) }}">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.75rem;font-weight:500;color:#6b7280;margin-bottom:4px;">E-Mail</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com"
                       style="width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:0.875rem;box-sizing:border-box;outline:none;"
                       onfocus="this.style.borderColor='{{ $brandColor }}'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:0.75rem;font-weight:500;color:#6b7280;margin-bottom:4px;">
                    Teléfono <span style="color:#ef4444;">*</span>
                </label>
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="3001234567" required
                       style="width:100%;border:1px solid {{ $errors->has('phone') ? '#ef4444' : '#e5e7eb' }};border-radius:8px;padding:10px 12px;font-size:0.875rem;box-sizing:border-box;outline:none;"
                       onfocus="this.style.borderColor='{{ $brandColor }}'" onblur="this.style.borderColor='#e5e7eb'">
            </div>

            <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:20px;cursor:pointer;">
                <input type="checkbox" name="accept_all" value="1" required
                       style="margin-top:2px;width:16px;height:16px;flex-shrink:0;accent-color:{{ $brandColor }};"
                       {{ old('accept_all') ? 'checked' : '' }}>
                <span style="font-size:0.78rem;color:#6b7280;line-height:1.4;">
                    Al marcar esta casilla autorizo el tratamiento de mis datos personales, acepto los
                    <a href="{{ route('public.legal.terms') }}" target="_blank" style="color:{{ $brandColor }};text-decoration:underline;">Términos y Condiciones</a>
                    y la
                    <a href="{{ route('public.legal.privacy') }}" target="_blank" style="color:{{ $brandColor }};text-decoration:underline;">Política de Privacidad</a>,
                    y consiento el envío de comunicaciones SMS, conforme a la Ley 1581 de 2012.
                </span>
            </label>

            <button type="submit"
                    style="width:100%;background:{{ $brandColor }};color:white;font-weight:700;font-size:1rem;padding:14px;border:none;border-radius:12px;cursor:pointer;transition:opacity .15s;"
                    onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                {{ $btnText }}
            </button>
        </form>
        <p style="font-size:0.7rem;color:#9ca3af;text-align:center;margin-top:12px;">
            Tu aceptación quedará registrada con fecha, hora e IP para cumplimiento legal.
        </p>
    </div>

    @else
    {{-- ── Success ── --}}
    @include('public._consent_body', compact('accepted','recipient','heading','subheading','bodyHtml','btnText','okHeading','okText','batch','customerName','brandColor','legalDoc','discountBadge'))
    @endif

    @include('public._consent_footer', compact('footerText','brandColor'))
</div>
@endif

<script>
function copyCode(el) {
    const text = el.textContent.trim();
    navigator.clipboard?.writeText(text).then(() => showToast('¡Código copiado!')).catch(() => fallbackCopy(text));
}
function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta); ta.select(); document.execCommand('copy');
    document.body.removeChild(ta); showToast('¡Código copiado!');
}
function showToast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#111827;color:white;padding:10px 20px;border-radius:9999px;font-size:14px;font-weight:500;z-index:999;transition:opacity .3s';
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2000);
}
</script>
</body>
</html>
