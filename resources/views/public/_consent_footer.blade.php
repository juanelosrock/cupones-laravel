@php $isDark = $dark ?? false; @endphp
<div class="w-full max-w-md mt-8 text-center">
    @if($footerText ?? null)
        <p class="text-xs {{ $isDark ? 'text-white/40' : 'text-gray-400' }} mb-2">{{ $footerText }}</p>
    @endif
    <div class="flex justify-center gap-4 text-xs {{ $isDark ? 'text-white/40' : 'text-gray-400' }}">
        <a href="{{ route('public.legal.privacy') }}" class="{{ $isDark ? 'hover:text-white/60' : 'hover:text-gray-600' }}">Política de privacidad</a>
        <span>·</span>
        <a href="{{ route('public.legal.terms') }}" class="{{ $isDark ? 'hover:text-white/60' : 'hover:text-gray-600' }}">Términos y condiciones</a>
        <span>·</span>
        <a href="{{ route('public.legal.sms') }}" class="{{ $isDark ? 'hover:text-white/60' : 'hover:text-gray-600' }}">Consentimiento SMS</a>
    </div>
    <p class="text-xs {{ $isDark ? 'text-white/20' : 'text-gray-300' }} mt-2">CuponesHub · Ley 1581 de 2012 (Colombia)</p>
</div>
