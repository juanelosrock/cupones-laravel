@extends('layouts.admin')
@section('title', 'Proveedores de Mensajería')
@section('content')

<div class="max-w-3xl">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Proveedores de Mensajería</h1>
        <p class="text-sm text-gray-500 mt-0.5">Configura y cambia los proveedores de SMS y Email activos</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.providers.update') }}" x-data="{
    smsDriver: '{{ $settings['sms_driver'] }}',
    emailDriver: '{{ $settings['email_driver'] }}',
    waDriver: '{{ $settings['whatsapp_driver'] }}'
}">
    @csrf
    @method('PUT')

    {{-- ══ SMS ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <div class="flex items-center gap-3 mb-5">
            <span class="text-xl">📱</span>
            <h2 class="text-base font-semibold text-gray-800">Proveedor de SMS</h2>
        </div>

        {{-- Driver selector --}}
        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-600 mb-2">Proveedor activo</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach(['log' => ['label'=>'Log (dev)','icon'=>'🗒️','desc'=>'Solo registra en logs'], 'zenvia' => ['label'=>'Zenvia','icon'=>'⚡','desc'=>'Plataforma activa'], 'infobip' => ['label'=>'Infobip','icon'=>'🌐','desc'=>'Proveedor alternativo'], 'labsmobile' => ['label'=>'LabsMobile','icon'=>'📲','desc'=>'SMS masivo']] as $val => $opt)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="sms_driver" value="{{ $val }}" x-model="smsDriver" class="sr-only peer">
                        <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-gray-300">
                            <div class="text-xl mb-1">{{ $opt['icon'] }}</div>
                            <p class="text-sm font-semibold text-gray-800">{{ $opt['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $opt['desc'] }}</p>
                        </div>
                        <span class="absolute top-2 right-2 hidden peer-checked:block">
                            <span class="w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Zenvia SMS config --}}
        <div x-show="smsDriver === 'zenvia'" x-transition class="border border-blue-100 rounded-xl p-4 bg-blue-50/40 space-y-3">
            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Configuración Zenvia SMS</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        API Token
                        @if(!empty($settings['sms_zenvia_token_set']))
                            <span class="ml-1 text-green-600 font-normal">✓ guardado</span>
                        @endif
                    </label>
                    <input type="password" name="sms_zenvia_token"
                           placeholder="{{ !empty($settings['sms_zenvia_token_set']) ? '••••••••••••••••' : 'Ingresa el token' }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Dejar vacío para mantener el token actual</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Remitente (alias)</label>
                    <input type="text" name="sms_zenvia_from" value="{{ $settings['sms_zenvia_from'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="CuponesHub">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Código de país</label>
                    <input type="text" name="sms_zenvia_country" value="{{ $settings['sms_zenvia_country'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="57">
                </div>
            </div>
        </div>

        {{-- Infobip SMS config --}}
        <div x-show="smsDriver === 'infobip'" x-transition class="border border-indigo-100 rounded-xl p-4 bg-indigo-50/40 space-y-3">
            <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">Configuración Infobip SMS</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        API Key
                        @if(!empty($settings['sms_infobip_api_key_set']))
                            <span class="ml-1 text-green-600 font-normal">✓ guardado</span>
                        @endif
                    </label>
                    <input type="password" name="sms_infobip_api_key"
                           placeholder="{{ !empty($settings['sms_infobip_api_key_set']) ? '••••••••••••••••' : 'Ingresa el API Key' }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Dejar vacío para mantener el valor actual</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Base URL</label>
                    <input type="url" name="sms_infobip_base_url" value="{{ $settings['sms_infobip_base_url'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="https://XXXXX.api.infobip.com">
                    <p class="text-xs text-gray-400 mt-0.5">URL personalizada de tu cuenta Infobip</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Remitente</label>
                    <input type="text" name="sms_infobip_from" value="{{ $settings['sms_infobip_from'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="CuponesHub">
                </div>
            </div>
        </div>

        {{-- LabsMobile SMS config --}}
        <div x-show="smsDriver === 'labsmobile'" x-transition class="border border-orange-100 rounded-xl p-4 bg-orange-50/40 space-y-3">
            <p class="text-xs font-semibold text-orange-700 uppercase tracking-wide">Configuración LabsMobile SMS</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Usuario (email)</label>
                    <input type="text" name="sms_labsmobile_username" value="{{ $settings['sms_labsmobile_username'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-300 outline-none"
                           placeholder="tu@correo.com">
                    <p class="text-xs text-gray-400 mt-0.5">Email de tu cuenta LabsMobile</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Token API
                        @if(!empty($settings['sms_labsmobile_token_set']))
                            <span class="ml-1 text-green-600 font-normal">✓ guardado</span>
                        @endif
                    </label>
                    <input type="password" name="sms_labsmobile_token"
                           placeholder="{{ !empty($settings['sms_labsmobile_token_set']) ? '••••••••••••••••' : 'Ingresa el token' }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-300 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Dejar vacío para mantener el token actual</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">TPOA (remitente)</label>
                    <input type="text" name="sms_labsmobile_tpoa" value="{{ $settings['sms_labsmobile_tpoa'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-300 outline-none"
                           placeholder="CuponesHub">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Código de país</label>
                    <input type="text" name="sms_labsmobile_country" value="{{ $settings['sms_labsmobile_country'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-300 outline-none"
                           placeholder="57">
                </div>
            </div>
        </div>

        {{-- Log notice --}}
        <div x-show="smsDriver === 'log'" x-transition class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <p class="text-sm text-gray-500">En modo <strong>log</strong> los SMS no se envían — solo se registran en <code class="bg-gray-100 px-1 rounded text-xs">storage/logs/laravel.log</code>. Útil para desarrollo.</p>
        </div>
    </div>

    {{-- ══ EMAIL ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <span class="text-xl">📧</span>
            <h2 class="text-base font-semibold text-gray-800">Proveedor de Email</h2>
        </div>

        {{-- Driver selector --}}
        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-600 mb-2">Proveedor activo</label>
            <div class="grid grid-cols-3 gap-3">
                @foreach(['log' => ['label'=>'Log (dev)','icon'=>'🗒️','desc'=>'Solo registra en logs, no envía'], 'zenvia' => ['label'=>'Zenvia','icon'=>'⚡','desc'=>'Plataforma activa'], 'infobip' => ['label'=>'Infobip','icon'=>'🌐','desc'=>'Proveedor alternativo']] as $val => $opt)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="email_driver" value="{{ $val }}" x-model="emailDriver" class="sr-only peer">
                        <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-gray-300">
                            <div class="text-xl mb-1">{{ $opt['icon'] }}</div>
                            <p class="text-sm font-semibold text-gray-800">{{ $opt['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $opt['desc'] }}</p>
                        </div>
                        <span class="absolute top-2 right-2 hidden peer-checked:block">
                            <span class="w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Zenvia Email config --}}
        <div x-show="emailDriver === 'zenvia'" x-transition class="border border-blue-100 rounded-xl p-4 bg-blue-50/40 space-y-3">
            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Configuración Zenvia Email</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        API Token
                        @if(!empty($settings['email_zenvia_token_set']))
                            <span class="ml-1 text-green-600 font-normal">✓ guardado</span>
                        @endif
                    </label>
                    <input type="password" name="email_zenvia_token"
                           placeholder="{{ !empty($settings['email_zenvia_token_set']) ? '••••••••••••••••' : 'Ingresa el token' }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Dejar vacío para mantener el token actual</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del remitente</label>
                    <input type="text" name="email_zenvia_from_name" value="{{ $settings['email_zenvia_from_name'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="CuponesHub">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email remitente</label>
                    <input type="email" name="email_zenvia_from_address" value="{{ $settings['email_zenvia_from_address'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="noreply@tudominio.com">
                    <p class="text-xs text-gray-400 mt-0.5">Debe estar verificado en Zenvia</p>
                </div>
            </div>
        </div>

        {{-- Infobip Email config --}}
        <div x-show="emailDriver === 'infobip'" x-transition class="border border-indigo-100 rounded-xl p-4 bg-indigo-50/40 space-y-3">
            <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">Configuración Infobip Email</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        API Key
                        @if(!empty($settings['email_infobip_api_key_set']))
                            <span class="ml-1 text-green-600 font-normal">✓ guardado</span>
                        @endif
                    </label>
                    <input type="password" name="email_infobip_api_key"
                           placeholder="{{ !empty($settings['email_infobip_api_key_set']) ? '••••••••••••••••' : 'Ingresa el API Key' }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Dejar vacío para mantener el valor actual</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Base URL</label>
                    <input type="url" name="email_infobip_base_url" value="{{ $settings['email_infobip_base_url'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="https://XXXXX.api.infobip.com">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del remitente</label>
                    <input type="text" name="email_infobip_from_name" value="{{ $settings['email_infobip_from_name'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="CuponesHub">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email remitente</label>
                    <input type="email" name="email_infobip_from_address" value="{{ $settings['email_infobip_from_address'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"
                           placeholder="noreply@tudominio.com">
                    <p class="text-xs text-gray-400 mt-0.5">Debe estar verificado en Infobip</p>
                </div>
            </div>
        </div>

        {{-- Log notice --}}
        <div x-show="emailDriver === 'log'" x-transition class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <p class="text-sm text-gray-500">En modo <strong>log</strong> los emails no se envían — solo se registran en <code class="bg-gray-100 px-1 rounded text-xs">storage/logs/laravel.log</code>. Útil para desarrollo.</p>
        </div>
    </div>

    {{-- ══ WHATSAPP ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <span class="text-xl">💬</span>
            <h2 class="text-base font-semibold text-gray-800">Proveedor de WhatsApp</h2>
        </div>

        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-600 mb-2">Proveedor activo</label>
            <div class="grid grid-cols-2 gap-3 max-w-sm">
                @foreach(['log' => ['label'=>'Log (dev)','icon'=>'🗒️','desc'=>'Solo registra en logs'], 'zenvia' => ['label'=>'Zenvia','icon'=>'⚡','desc'=>'WhatsApp Business']] as $val => $opt)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="whatsapp_driver" value="{{ $val }}" x-model="waDriver" class="sr-only peer">
                        <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 border-gray-200 hover:border-gray-300">
                            <div class="text-xl mb-1">{{ $opt['icon'] }}</div>
                            <p class="text-sm font-semibold text-gray-800">{{ $opt['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $opt['desc'] }}</p>
                        </div>
                        <span class="absolute top-2 right-2 hidden peer-checked:block">
                            <span class="w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Zenvia WhatsApp config --}}
        <div x-show="waDriver === 'zenvia'" x-transition class="border border-green-100 rounded-xl p-4 bg-green-50/40 space-y-3">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Configuración Zenvia WhatsApp</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        API Token
                        @if(!empty($settings['whatsapp_zenvia_token_set']))
                            <span class="ml-1 text-green-600 font-normal">✓ guardado</span>
                        @endif
                    </label>
                    <input type="password" name="whatsapp_zenvia_token"
                           placeholder="{{ !empty($settings['whatsapp_zenvia_token_set']) ? '••••••••••••••••' : 'Ingresa el token Zenvia' }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-300 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Mismo token que SMS Zenvia si comparten cuenta. Dejar vacío para mantener.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Número WhatsApp Business (from)</label>
                    <input type="text" name="whatsapp_zenvia_from" value="{{ $settings['whatsapp_zenvia_from'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="573001234567">
                    <p class="text-xs text-gray-400 mt-0.5">Número registrado en Zenvia, con código de país, sin +</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Código de país (destino)</label>
                    <input type="text" name="whatsapp_zenvia_country" value="{{ $settings['whatsapp_zenvia_country'] }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-300 outline-none"
                           placeholder="57">
                </div>
            </div>
        </div>

        {{-- Log notice --}}
        <div x-show="waDriver === 'log'" x-transition class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <p class="text-sm text-gray-500">En modo <strong>log</strong> los mensajes WhatsApp no se envían — solo se registran en <code class="bg-gray-100 px-1 rounded text-xs">storage/logs/laravel.log</code>.</p>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
            Guardar configuración
        </button>
        <p class="text-xs text-gray-400">Los cambios se aplican de inmediato, sin reiniciar el servidor.</p>
    </div>
</form>

</div>
@endsection
