<div class="w-full max-w-md mt-8 text-center">
    @if($config->footer_text)
        <p class="text-xs {{ ($dark ?? false) ? 'text-white/40' : 'text-gray-400' }} mb-2">{{ $config->footer_text }}</p>
    @endif
    <div class="flex justify-center gap-4 text-xs {{ ($dark ?? false) ? 'text-white/40' : 'text-gray-400' }}">
        <a href="#" class="{{ ($dark ?? false) ? 'hover:text-white/60' : 'hover:text-gray-600' }}">Política de privacidad</a>
        <span>·</span>
        <a href="#" class="{{ ($dark ?? false) ? 'hover:text-white/60' : 'hover:text-gray-600' }}">Términos y condiciones</a>
        <span>·</span>
        <a href="#" class="{{ ($dark ?? false) ? 'hover:text-white/60' : 'hover:text-gray-600' }}">Consentimiento SMS</a>
    </div>
    <p class="text-xs {{ ($dark ?? false) ? 'text-white/25' : 'text-gray-300' }} mt-2">Ley 1581 de 2012 (Colombia)</p>
</div>
