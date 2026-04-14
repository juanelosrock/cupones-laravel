@extends('layouts.admin')
@section('title', 'Nueva Campaña SMS')
@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.sms-campaigns.index') }}" class="hover:text-gray-600">Campañas SMS</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Nueva campaña</span>
</div>

<div class="max-w-2xl"
     x-data="smsCreate({{ json_encode($campaignData) }})"
     x-init="init()">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nueva Campaña SMS</h1>
        <p class="text-sm text-gray-500 mt-1">Envía SMS masivos con cupones de descuento a los clientes de una campaña.</p>
    </div>

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.sms-campaigns.store') }}">
        @csrf

        {{-- 1: Nombre --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">1. Datos básicos</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la campaña <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="Ej: Promo Diciembre — SMS Bogotá"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- 2: Campaña --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">2. Seleccionar campaña y clientes</h2>

            @if($campaigns->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                    <strong>No hay campañas con clientes disponibles.</strong><br>
                    Primero importa clientes a una campaña desde
                    <a href="{{ route('admin.campaigns.index') }}" class="underline">Campañas</a>.
                </div>
            @else
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaña de origen <span class="text-red-500">*</span></label>
                    <select name="campaign_id" required
                            @change="selectCampaign($event.target.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('campaign_id') border-red-400 @enderror">
                        <option value="">— Selecciona una campaña —</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}"
                                {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                {{ $campaign->name }} ({{ number_format($campaign->campaign_customers_count) }} clientes)
                            </option>
                        @endforeach
                    </select>
                    @error('campaign_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <template x-if="selectedCampaign">
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-center gap-3">
                        <div class="text-2xl">👥</div>
                        <div>
                            <p class="text-sm font-semibold text-blue-800" x-text="selectedCampaign.customer_count + ' clientes activos'"></p>
                            <p class="text-xs text-blue-600">Recibirán el SMS como destinatarios de esta campaña</p>
                        </div>
                    </div>
                </template>
                <template x-if="!selectedCampaign">
                    <p class="text-xs text-gray-400 mt-1">Selecciona una campaña para ver el número de destinatarios.</p>
                </template>
            @endif
        </div>

        {{-- 3: Lote de cupones --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">3. Lote de cupones <span class="text-gray-400 font-normal">(opcional)</span></h2>
            <p class="text-xs text-gray-500 mb-4">Asocia un lote para incluir el código con la variable <code class="bg-gray-100 px-1 rounded">{code}</code>.</p>

            <template x-if="selectedCampaign && selectedCampaign.batches.length > 0">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lote de cupones</label>
                    <select name="coupon_batch_id"
                            x-model="selectedBatchId"
                            @change="selectBatch($event.target.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Sin cupón —</option>
                        <template x-for="batch in selectedCampaign.batches" :key="batch.id">
                            <option :value="String(batch.id)" x-text="batch.label"></option>
                        </template>
                    </select>

                    <template x-if="selectedBatch">
                        <div class="mt-3 p-3 bg-green-50 border border-green-100 rounded-lg">
                            <p class="text-xs font-semibold text-green-800">Descuento:
                                <span x-text="selectedBatch.discount_type === 'percentage'
                                    ? selectedBatch.discount_value + '%'
                                    : '$ ' + Number(selectedBatch.discount_value).toLocaleString('es-CO')">
                                </span>
                            </p>
                            <template x-if="selectedBatch.code_type === 'general'">
                                <p class="text-xs text-green-700 mt-0.5">
                                    Código único: <strong x-text="selectedBatch.general_code"></strong>
                                    (todos reciben el mismo código)
                                </p>
                            </template>
                            <template x-if="selectedBatch.code_type !== 'general'">
                                <p class="text-xs text-green-700 mt-0.5">
                                    Códigos únicos por cliente (prefijo: <strong x-text="selectedBatch.prefix"></strong>)
                                </p>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!selectedCampaign">
                <p class="text-xs text-gray-400">Selecciona una campaña primero.</p>
            </template>
            <template x-if="selectedCampaign && selectedCampaign.batches.length === 0">
                <p class="text-xs text-gray-400">La campaña seleccionada no tiene lotes de cupones activos.</p>
            </template>
        </div>

        {{-- 4: Consentimiento --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">4. Autorización de datos <span class="text-gray-400 font-normal">(opcional)</span></h2>
            <p class="text-xs text-gray-500 mb-4">Incluye un enlace de autorización en el SMS. El cliente acepta el uso de datos antes de ver su código.</p>

            <label class="flex items-start gap-3 cursor-pointer select-none">
                <input type="checkbox" name="send_consent_link" value="1"
                       x-model="sendConsentLink"
                       {{ old('send_consent_link') ? 'checked' : '' }}
                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <p class="text-sm font-medium text-gray-800">Enviar enlace de autorización de datos</p>
                    <p class="text-xs text-gray-500 mt-0.5">El SMS incluirá la variable <code class="bg-gray-100 px-1 rounded">{link}</code> con una URL personalizada por cliente. Al hacer clic, el cliente acepta el tratamiento de datos y se le revela el código de descuento.</p>
                </div>
            </label>

            <template x-if="sendConsentLink">
                <div class="mt-4 space-y-4">
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg">
                        <p class="text-xs font-semibold text-blue-800 mb-1">Flujo de consentimiento:</p>
                        <ol class="text-xs text-blue-700 space-y-1 list-decimal list-inside">
                            <li>Se genera un enlace único por cliente (ej: <span class="font-mono bg-blue-100 px-1 rounded">/autorizar/abc123...</span>)</li>
                            <li>El cliente abre el enlace, lee el aviso de privacidad y acepta</li>
                            <li>Se registra la aceptación con IP y timestamp (Ley 1581)</li>
                            <li>El código de descuento queda visible en pantalla</li>
                        </ol>
                        <p class="text-xs text-blue-600 mt-2">Usa <code class="bg-blue-100 px-1 rounded">{link}</code> en el mensaje. Ejemplo: <em>"Acepta y obtén tu código: {link}"</em></p>
                    </div>

                    {{-- Landing page selector --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Diseño de la página de autorización
                        </label>
                        <select name="landing_config_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">
                                @if($landingConfigs->where('is_default', true)->first())
                                    Usar landing por defecto ({{ $landingConfigs->where('is_default', true)->first()->name }})
                                @else
                                    Usar diseño estándar del sistema
                                @endif
                            </option>
                            @foreach($landingConfigs as $lc)
                            <option value="{{ $lc->id }}"
                                    {{ old('landing_config_id') == $lc->id ? 'selected' : '' }}>
                                [{{ strtoupper($lc->template) }}] {{ $lc->name }}
                                @if($lc->is_default) ★ @endif
                            </option>
                            @endforeach
                        </select>
                        @if($landingConfigs->isEmpty())
                        <p class="mt-1 text-xs text-gray-400">
                            No tienes landing pages creadas.
                            <a href="{{ route('admin.landing-configs.create') }}" target="_blank"
                               class="text-blue-600 hover:underline">Crear una →</a>
                        </p>
                        @else
                        <p class="mt-1 text-xs text-gray-400">
                            <a href="{{ route('admin.landing-configs.index') }}" target="_blank"
                               class="text-blue-600 hover:underline">Gestionar landing pages →</a>
                        </p>
                        @endif
                    </div>
                </div>
            </template>
        </div>

        {{-- 5: Mensaje --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">5. Mensaje SMS</h2>
            <p class="text-xs text-gray-500 mb-4">Máximo 160 caracteres. Variables disponibles:</p>

            <div class="flex gap-2 mb-3 flex-wrap">
                <button type="button" @click="insertVar('{name}')"
                        class="text-xs bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 px-2 py-1 rounded font-mono transition-colors">
                    {name}
                </button>
                <button type="button" @click="insertVar('{code}')"
                        class="text-xs bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 px-2 py-1 rounded font-mono transition-colors">
                    {code}
                </button>
                <button type="button" @click="insertVar('{discount}')"
                        class="text-xs bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 px-2 py-1 rounded font-mono transition-colors">
                    {discount}
                </button>
                <button type="button" @click="insertVar('{phone}')"
                        class="text-xs bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 px-2 py-1 rounded font-mono transition-colors">
                    {phone}
                </button>
                <template x-if="sendConsentLink">
                    <button type="button" @click="insertVar('{link}')"
                            class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded font-mono transition-colors font-semibold">
                        {link}
                    </button>
                </template>
            </div>

            <textarea name="message_template" id="msg-template" rows="4" required
                      maxlength="160"
                      x-model="message"
                      @input="charCount = $event.target.value.length"
                      placeholder="Ej: Hola {name}, usa el código {code} para obtener {discount} de descuento. ¡Válido esta semana!"
                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('message_template') border-red-400 @enderror">{{ old('message_template') }}</textarea>

            <div class="flex items-center justify-between mt-1">
                <span>@error('message_template')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</span>
                <span class="text-xs font-mono" :class="charCount > 140 ? 'text-orange-500 font-semibold' : (charCount > 155 ? 'text-red-500 font-bold' : 'text-gray-400')">
                    <span x-text="charCount"></span>/160
                </span>
            </div>

            <template x-if="message.length > 0">
                <div class="mt-4 p-4 bg-gray-50 border border-gray-100 rounded-xl">
                    <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Vista previa</p>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 max-w-xs">
                        <p class="text-xs text-gray-800 leading-relaxed" x-text="previewMessage()"></p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Las variables se reemplazarán con datos reales al enviar.</p>
                </div>
            </template>
        </div>

        {{-- 6: Programar --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">6. Programar envío <span class="text-gray-400 font-normal">(opcional)</span></h2>
            <p class="text-xs text-gray-500 mb-4">Deja en blanco para enviar manualmente desde el detalle de la campaña.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y hora de envío</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('scheduled_at') border-red-400 @enderror">
                @error('scheduled_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Driver notice --}}
        @php $driver = config('services.sms.driver', 'log'); @endphp
        @if($driver === 'log')
            <div class="mb-5 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-yellow-800">Modo desarrollo — Driver: log</p>
                <p class="text-xs text-yellow-700 mt-1">
                    Los SMS se registrarán en <code class="bg-yellow-100 px-1 rounded">storage/logs/laravel.log</code>.
                    Para activar Zenvia: <code class="bg-yellow-100 px-1 rounded">SMS_DRIVER=zenvia</code> +
                    <code class="bg-yellow-100 px-1 rounded">SMS_ZENVIA_TOKEN=tu_token</code> en <code>.env</code>.
                </p>
            </div>
        @elseif($driver === 'zenvia')
            <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <span class="text-green-500 text-xl">✓</span>
                <div>
                    <p class="text-sm font-semibold text-green-800">Driver activo: Zenvia</p>
                    <p class="text-xs text-green-700">Los SMS se enviarán a través de la API de Zenvia.</p>
                </div>
            </div>
        @endif

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Crear campaña SMS
            </button>
            <a href="{{ route('admin.sms-campaigns.index') }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function smsCreate(campaignData) {
    return {
        campaignData: campaignData,
        selectedCampaign: null,
        selectedBatch: null,
        selectedBatchId: @json(old('coupon_batch_id', '')),
        sendConsentLink: @json((bool) old('send_consent_link', false)),
        message: @json(old('message_template', '')),
        charCount: {{ strlen(old('message_template', '')) }},

        init() {
            const oldCampaignId = @json(old('campaign_id', ''));
            if (oldCampaignId && this.campaignData[oldCampaignId]) {
                this.selectedCampaign = this.campaignData[oldCampaignId];
            }
            const oldBatchId = @json(old('coupon_batch_id', ''));
            if (oldBatchId && this.selectedCampaign) {
                this.selectedBatch = this.selectedCampaign.batches.find(b => String(b.id) === String(oldBatchId)) || null;
            }
            // Auto-select if campaign has only one batch and no old selection
            if (this.selectedCampaign && !oldBatchId && this.selectedCampaign.batches.length === 1) {
                const batch = this.selectedCampaign.batches[0];
                this.selectedBatchId = String(batch.id);
                this.selectedBatch = batch;
            }
        },

        selectCampaign(id) {
            this.selectedCampaign = this.campaignData[id] || null;
            this.selectedBatch = null;
            this.selectedBatchId = '';
            // Auto-select if there is exactly one batch
            if (this.selectedCampaign && this.selectedCampaign.batches.length === 1) {
                this.$nextTick(() => {
                    const batch = this.selectedCampaign.batches[0];
                    this.selectedBatchId = String(batch.id);
                    this.selectedBatch = batch;
                });
            }
        },

        selectBatch(id) {
            this.selectedBatchId = id;
            if (!id || !this.selectedCampaign) { this.selectedBatch = null; return; }
            this.selectedBatch = this.selectedCampaign.batches.find(b => String(b.id) === String(id)) || null;
        },

        insertVar(v) {
            const ta = document.getElementById('msg-template');
            const start = ta.selectionStart ?? this.message.length;
            const end = ta.selectionEnd ?? this.message.length;
            this.message = this.message.substring(0, start) + v + this.message.substring(end);
            this.charCount = this.message.length;
            this.$nextTick(() => {
                ta.selectionStart = ta.selectionEnd = start + v.length;
                ta.focus();
            });
        },

        previewMessage() {
            let msg = this.message;
            const code = this.selectedBatch
                ? (this.selectedBatch.code_type === 'general' ? this.selectedBatch.general_code : (this.selectedBatch.prefix || '') + 'XXXXXXXX')
                : 'CODIGO123';
            const discount = this.selectedBatch
                ? (this.selectedBatch.discount_type === 'percentage'
                    ? this.selectedBatch.discount_value + '%'
                    : '$ ' + Number(this.selectedBatch.discount_value).toLocaleString('es-CO'))
                : '20%';
            return msg
                .replace(/{name}/g, 'María García')
                .replace(/{code}/g, code)
                .replace(/{discount}/g, discount)
                .replace(/{phone}/g, '3001234567')
                .replace(/{link}/g, 'https://tudominio.com/autorizar/abc123…');
        }
    }
}
</script>

@endsection
