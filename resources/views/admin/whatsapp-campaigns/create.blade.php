@extends('layouts.admin')
@section('title', 'Nueva Campaña WhatsApp')
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.whatsapp-campaigns.index') }}" class="hover:text-gray-600">Campañas WhatsApp</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Nueva campaña</span>
</div>

<div class="max-w-2xl"
     x-data="waCreate({{ json_encode($campaignData) }})"
     x-init="init()">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nueva Campaña WhatsApp</h1>
        <p class="text-sm text-gray-500 mt-1">Envía mensajes masivos por WhatsApp con cupones de descuento.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.whatsapp-campaigns.store') }}">
        @csrf

        {{-- 1: Nombre --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">1. Datos básicos</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la campaña <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="Ej: Promo Abril — WhatsApp"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- 2: Campaña --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">2. Campaña y clientes</h2>

            @if($campaigns->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                    <strong>No hay campañas con clientes disponibles.</strong>
                    Importa clientes desde <a href="{{ route('admin.campaigns.index') }}" class="underline">Campañas</a>.
                </div>
            @else
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaña de origen <span class="text-red-500">*</span></label>
                    <select name="campaign_id" required
                            @change="selectCampaign($event.target.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('campaign_id') border-red-400 @enderror">
                        <option value="">— Selecciona una campaña —</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                {{ $campaign->name }} ({{ number_format($campaign->campaign_customers_count) }} clientes)
                            </option>
                        @endforeach
                    </select>
                    @error('campaign_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <template x-if="selectedCampaign">
                    <div class="bg-green-50 border border-green-100 rounded-lg p-3 flex items-center gap-3">
                        <div class="text-2xl">👥</div>
                        <div>
                            <p class="text-sm font-semibold text-green-800" x-text="selectedCampaign.customer_count + ' clientes activos'"></p>
                            <p class="text-xs text-green-600">Recibirán el mensaje WhatsApp como destinatarios</p>
                        </div>
                    </div>
                </template>
            @endif
        </div>

        {{-- 3: Lote de cupones --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">3. Lote de cupones <span class="text-gray-400 font-normal">(opcional)</span></h2>
            <p class="text-xs text-gray-500 mb-4">Usa <code class="bg-gray-100 px-1 rounded">{code}</code> y <code class="bg-gray-100 px-1 rounded">{discount}</code> en el mensaje o en los campos del template.</p>

            <template x-if="selectedCampaign && selectedCampaign.batches.length > 0">
                <div>
                    <select name="coupon_batch_id"
                            x-model="selectedBatchId"
                            @change="selectBatch($event.target.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">— Sin cupón —</option>
                        <template x-for="batch in selectedCampaign.batches" :key="batch.id">
                            <option :value="String(batch.id)" x-text="batch.label"></option>
                        </template>
                    </select>
                    <template x-if="selectedBatch">
                        <div class="mt-3 p-3 bg-green-50 border border-green-100 rounded-lg">
                            <p class="text-xs font-semibold text-green-800">
                                Descuento: <span x-text="selectedBatch.discount_type === 'percentage' ? selectedBatch.discount_value + '%' : '$ ' + Number(selectedBatch.discount_value).toLocaleString('es-CO')"></span>
                            </p>
                            <template x-if="selectedBatch.code_type === 'general'">
                                <p class="text-xs text-green-700 mt-0.5">Código: <strong x-text="selectedBatch.general_code"></strong></p>
                            </template>
                            <template x-if="selectedBatch.code_type !== 'general'">
                                <p class="text-xs text-green-700 mt-0.5">Códigos únicos — prefijo: <strong x-text="selectedBatch.prefix"></strong></p>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!selectedCampaign">
                <p class="text-xs text-gray-400">Selecciona una campaña primero.</p>
            </template>
            <template x-if="selectedCampaign && selectedCampaign.batches.length === 0">
                <p class="text-xs text-gray-400">La campaña no tiene lotes de cupones activos.</p>
            </template>
        </div>

        {{-- 4: Tipo de contenido --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">4. Tipo de mensaje</h2>
            <p class="text-xs text-gray-500 mb-4">
                WhatsApp exige <strong>plantillas pre-aprobadas</strong> para envíos masivos iniciados por la empresa (BIM).
                El texto libre solo funciona en sesiones activas (el cliente escribió primero en las últimas 24 h).
            </p>

            <div class="grid grid-cols-2 gap-3 mb-5">
                <label class="relative cursor-pointer">
                    <input type="radio" name="content_type" value="template" x-model="contentType" class="sr-only peer">
                    <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 border-gray-200 hover:border-gray-300">
                        <div class="text-xl mb-1">📋</div>
                        <p class="text-sm font-semibold text-gray-800">Plantilla aprobada</p>
                        <p class="text-xs text-gray-500 mt-0.5">Recomendado para envíos masivos. Requiere template registrado en Zenvia.</p>
                    </div>
                    <span class="absolute top-2 right-2 hidden peer-checked:block">
                        <span class="w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                    </span>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" name="content_type" value="text" x-model="contentType" class="sr-only peer">
                    <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-yellow-500 peer-checked:bg-yellow-50 border-gray-200 hover:border-gray-300">
                        <div class="text-xl mb-1">💬</div>
                        <p class="text-sm font-semibold text-gray-800">Texto libre</p>
                        <p class="text-xs text-gray-500 mt-0.5">Solo para sesiones activas o pruebas. No válido para BIM.</p>
                    </div>
                    <span class="absolute top-2 right-2 hidden peer-checked:block">
                        <span class="w-4 h-4 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                    </span>
                </label>
            </div>

            {{-- TEMPLATE MODE --}}
            <div x-show="contentType === 'template'" x-transition class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-xs text-green-800 space-y-1">
                    <p class="font-semibold">¿Cómo funciona una plantilla Zenvia?</p>
                    <ol class="list-decimal list-inside space-y-1 text-green-700">
                        <li>Registra y aprueba tu plantilla en el panel de Zenvia (con variables como <code class="bg-green-100 px-0.5 rounded">@{{1}}</code>, <code class="bg-green-100 px-0.5 rounded">@{{2}}</code> o nombres personalizados)</li>
                        <li>Copia el <strong>Template ID</strong> (UUID) desde Zenvia</li>
                        <li>Define aquí el valor de cada variable usando <code class="bg-green-100 px-0.5 rounded">{name}</code>, <code class="bg-green-100 px-0.5 rounded">{code}</code>, <code class="bg-green-100 px-0.5 rounded">{discount}</code></li>
                    </ol>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template ID <span class="text-red-500">*</span></label>
                    <input type="text" name="template_id" value="{{ old('template_id') }}"
                           placeholder="ej: a1b2c3d4-e5f6-7890-abcd-ef1234567890"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500 @error('template_id') border-red-400 @enderror">
                    @error('template_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-0.5">UUID del template registrado en tu cuenta Zenvia</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Variables del template</label>
                    <p class="text-xs text-gray-500 mb-2">
                        Define el valor de cada variable de tu template. La clave debe coincidir exactamente con el nombre de la variable en Zenvia
                        (ej: <code class="bg-gray-100 px-1 rounded">1</code>, <code class="bg-gray-100 px-1 rounded">2</code> o <code class="bg-gray-100 px-1 rounded">name</code>).
                        Puedes usar <code class="bg-gray-100 px-1 rounded">{name}</code>, <code class="bg-gray-100 px-1 rounded">{code}</code>, <code class="bg-gray-100 px-1 rounded">{discount}</code>, <code class="bg-gray-100 px-1 rounded">{phone}</code>.
                    </p>

                    <div class="space-y-2" x-data>
                        <template x-for="(field, idx) in templateFields" :key="idx">
                            <div class="flex gap-2 items-center">
                                <input type="text"
                                       :name="'template_fields[' + field.key + ']'"
                                       x-model="field.key"
                                       placeholder="clave (ej: 1)"
                                       class="w-28 border border-gray-200 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-green-300 outline-none">
                                <span class="text-gray-400 text-sm">→</span>
                                <input type="text"
                                       :name="'template_fields[' + field.key + ']'"
                                       x-model="field.value"
                                       placeholder="valor (ej: {name})"
                                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-green-300 outline-none">
                                <button type="button" @click="templateFields.splice(idx, 1)"
                                        class="text-red-400 hover:text-red-600 text-lg leading-none px-1">×</button>
                            </div>
                        </template>

                        <button type="button" @click="templateFields.push({key:'', value:''})"
                                class="text-xs text-green-600 hover:text-green-800 font-medium flex items-center gap-1 mt-2">
                            + Añadir variable
                        </button>
                    </div>

                    {{-- Hidden inputs para los campos del template (manejados por Alpine) --}}
                    <template x-for="field in templateFields" :key="field.key">
                        <input type="hidden" :name="'template_fields[' + field.key + ']'" :value="field.value">
                    </template>

                    <div class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-lg">
                        <p class="text-xs font-semibold text-gray-500 mb-2">Ejemplo de payload que se enviará a Zenvia:</p>
                        <pre class="text-xs text-gray-700 font-mono whitespace-pre-wrap" x-text="previewTemplate()"></pre>
                    </div>
                </div>
            </div>

            {{-- TEXT MODE --}}
            <div x-show="contentType === 'text'" x-transition class="space-y-3">
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-xs text-yellow-800">
                    ⚠️ El texto libre solo funciona si el destinatario te escribió en las últimas 24 h. Para envíos masivos usa <strong>Plantilla aprobada</strong>.
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-2">Variables: <code class="bg-gray-100 px-1 rounded">{name}</code> <code class="bg-gray-100 px-1 rounded">{code}</code> <code class="bg-gray-100 px-1 rounded">{discount}</code> <code class="bg-gray-100 px-1 rounded">{phone}</code></p>
                    <div class="flex gap-2 mb-2 flex-wrap">
                        @foreach(['{name}', '{code}', '{discount}', '{phone}'] as $var)
                            <button type="button" @click="insertVar('{{ $var }}')"
                                    class="text-xs bg-gray-100 hover:bg-green-100 hover:text-green-700 text-gray-700 px-2 py-1 rounded font-mono transition-colors">{{ $var }}</button>
                        @endforeach
                    </div>
                    <textarea name="message_template" id="msg-template" rows="5"
                              x-model="message"
                              @input="charCount = $event.target.value.length"
                              placeholder="Hola {name} 👋 Tu código de descuento es *{code}* — {discount} de descuento."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none @error('message_template') border-red-400 @enderror">{{ old('message_template') }}</textarea>
                    <div class="flex justify-end mt-1">
                        <span class="text-xs font-mono text-gray-400"><span x-text="charCount"></span> caracteres</span>
                    </div>
                    @error('message_template')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                    <template x-if="message.length > 0">
                        <div class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-xl">
                            <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Vista previa</p>
                            <div class="bg-[#dcf8c6] rounded-lg p-3 shadow-sm max-w-xs ml-auto">
                                <p class="text-xs text-gray-800 leading-relaxed whitespace-pre-wrap" x-text="previewText()"></p>
                                <p class="text-right text-[10px] text-gray-400 mt-1">✓✓</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- 5: Programar --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">5. Programar envío <span class="text-gray-400 font-normal">(opcional)</span></h2>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            @error('scheduled_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Driver notice --}}
        @php $waDriver = \App\Models\Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log'); @endphp
        @if($waDriver === 'log')
            <div class="mb-5 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-yellow-800">Modo desarrollo — Driver: log</p>
                <p class="text-xs text-yellow-700 mt-1">Los mensajes se registrarán en el log.
                    Configura las credenciales en <a href="{{ route('admin.providers.index') }}" class="underline">Proveedores</a>.</p>
            </div>
        @elseif($waDriver === 'zenvia')
            <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <span class="text-green-500 text-xl">✓</span>
                <p class="text-sm font-semibold text-green-800">Driver activo: Zenvia WhatsApp</p>
            </div>
        @endif

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Crear campaña WhatsApp
            </button>
            <a href="{{ route('admin.whatsapp-campaigns.index') }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function waCreate(campaignData) {
    return {
        campaignData,
        selectedCampaign: null,
        selectedBatch: null,
        selectedBatchId: @json(old('coupon_batch_id', '')),
        contentType: @json(old('content_type', 'template')),
        message: @json(old('message_template', '')),
        charCount: {{ strlen(old('message_template', '')) }},
        templateFields: @json(
            old('template_fields')
                ? collect(old('template_fields'))->map(fn($v, $k) => ['key' => $k, 'value' => $v])->values()
                : []
        ),

        init() {
            const oldId = @json(old('campaign_id', ''));
            if (oldId && this.campaignData[oldId]) {
                this.selectedCampaign = this.campaignData[oldId];
                const oldBatch = @json(old('coupon_batch_id', ''));
                if (oldBatch) {
                    this.selectedBatch = this.selectedCampaign.batches.find(b => String(b.id) === String(oldBatch)) || null;
                }
            }
            if (this.templateFields.length === 0) {
                this.templateFields = [{key: '1', value: '{name}'}, {key: '2', value: '{code}'}, {key: '3', value: '{discount}'}];
            }
        },

        selectCampaign(id) {
            this.selectedCampaign = this.campaignData[id] || null;
            this.selectedBatch = null;
            this.selectedBatchId = '';
            if (this.selectedCampaign?.batches.length === 1) {
                this.$nextTick(() => {
                    const b = this.selectedCampaign.batches[0];
                    this.selectedBatchId = String(b.id);
                    this.selectedBatch = b;
                });
            }
        },

        selectBatch(id) {
            this.selectedBatch = !id || !this.selectedCampaign ? null
                : this.selectedCampaign.batches.find(b => String(b.id) === String(id)) || null;
        },

        insertVar(v) {
            const ta = document.getElementById('msg-template');
            if (!ta) return;
            const start = ta.selectionStart ?? this.message.length;
            const end   = ta.selectionEnd   ?? this.message.length;
            this.message = this.message.substring(0, start) + v + this.message.substring(end);
            this.charCount = this.message.length;
            this.$nextTick(() => { ta.selectionStart = ta.selectionEnd = start + v.length; ta.focus(); });
        },

        previewText() {
            const code = this.selectedBatch
                ? (this.selectedBatch.code_type === 'general' ? this.selectedBatch.general_code : (this.selectedBatch.prefix || '') + 'XXXXXXXX')
                : 'PROMO25';
            const discount = this.selectedBatch
                ? (this.selectedBatch.discount_type === 'percentage' ? this.selectedBatch.discount_value + '%' : '$ ' + Number(this.selectedBatch.discount_value).toLocaleString('es-CO'))
                : '20%';
            return this.message
                .replace(/{name}/g, 'María García')
                .replace(/{code}/g, code)
                .replace(/{discount}/g, discount)
                .replace(/{phone}/g, '3001234567');
        },

        previewTemplate() {
            if (!this.templateFields.length) return '(sin variables definidas)';
            const fields = {};
            this.templateFields.forEach(f => { if (f.key) fields[f.key] = f.value || ''; });
            return JSON.stringify({
                type: 'template',
                templateId: document.querySelector('[name=template_id]')?.value || '<template-id>',
                fields
            }, null, 2);
        }
    }
}
</script>

@endsection
