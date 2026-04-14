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
@else
<div class="min-h-screen flex flex-col items-center justify-start pt-10 pb-12 px-4 relative"
     style="font-family: system-ui, -apple-system, sans-serif;">

    {{-- Background --}}
    @if($heroUrl)
    <div class="fixed inset-0 -z-10">
        <img src="{{ $heroUrl }}" class="w-full h-full object-cover" alt="">
        <div class="absolute inset-0 bg-black/55"></div>
    </div>
    @else
    <div class="fixed inset-0 -z-10"
         style="background: linear-gradient(135deg, {{ $brandColor }} 0%, #0f172a 100%)"></div>
    @endif

    {{-- Logo --}}
    <div class="w-full max-w-md mb-6 text-center">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="h-12 mx-auto object-contain"
                 style="filter: brightness(0) invert(1) drop-shadow(0 2px 6px rgba(0,0,0,0.5))" alt="logo">
        @else
            <div class="inline-flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <span class="text-white font-bold">C</span>
                </div>
                <span class="font-bold text-white text-xl drop-shadow">CuponesHub</span>
            </div>
        @endif
        @if($discountBadge)
        <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1 rounded-full mt-2">
            🎁 {{ $discountBadge }} te espera
        </div>
        @endif
    </div>

    @include('public._consent_body', compact('accepted','recipient','heading','subheading','bodyHtml','btnText','okHeading','okText','batch','customerName','brandColor','legalDoc','discountBadge'), ['heroMode' => true])

    @include('public._consent_footer', compact('footerText','brandColor'), ['dark' => true])
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
