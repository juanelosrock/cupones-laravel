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
                       placeholder="Ej: Promo Diciembre — WhatsApp Bogotá"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-400 @enderror">
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
                <template x-if="!selectedCampaign">
                    <p class="text-xs text-gray-400 mt-1">Selecciona una campaña para ver el número de destinatarios.</p>
                </template>
            @endif
        </div>

        {{-- 3: Lote de cupones --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">3. Lote de cupones <span class="text-gray-400 font-normal">(opcional)</span></h2>
            <p class="text-xs text-gray-500 mb-4">Incluye el código con la variable <code class="bg-gray-100 px-1 rounded">{code}</code>.</p>

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
                            <p class="text-xs font-semibold text-green-800">Descuento:
                                <span x-text="selectedBatch.discount_type === 'percentage'
                                    ? selectedBatch.discount_value + '%'
                                    : '$ ' + Number(selectedBatch.discount_value).toLocaleString('es-CO')">
                                </span>
                            </p>
                            <template x-if="selectedBatch.code_type === 'general'">
                                <p class="text-xs text-green-700 mt-0.5">Código: <strong x-text="selectedBatch.general_code"></strong> (todos reciben el mismo)</p>
                            </template>
                            <template x-if="selectedBatch.code_type !== 'general'">
                                <p class="text-xs text-green-700 mt-0.5">Códigos únicos por cliente (prefijo: <strong x-text="selectedBatch.prefix"></strong>)</p>
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

        {{-- 4: Mensaje --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">4. Mensaje WhatsApp</h2>
            <p class="text-xs text-gray-500 mb-4">Máximo 1000 caracteres. Variables disponibles:</p>

            <div class="flex gap-2 mb-3 flex-wrap">
                @foreach(['{name}', '{code}', '{discount}', '{phone}'] as $var)
                    <button type="button" @click="insertVar('{{ $var }}')"
                            class="text-xs bg-gray-100 hover:bg-green-100 hover:text-green-700 text-gray-700 px-2 py-1 rounded font-mono transition-colors">
                        {{ $var }}
                    </button>
                @endforeach
            </div>

            <textarea name="message_template" id="msg-template" rows="5" required
                      maxlength="1000"
                      x-model="message"
                      @input="charCount = $event.target.value.length"
                      placeholder="Ej: Hola {name} 👋 Tu código de descuento es *{code}* para obtener {discount} de descuento. ¡Válido esta semana! 🎉"
                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none @error('message_template') border-red-400 @enderror">{{ old('message_template') }}</textarea>

            <div class="flex items-center justify-between mt-1">
                <span>@error('message_template')<p class="text-xs text-red-600">{{ $message }}</p>@enderror</span>
                <span class="text-xs font-mono" :class="charCount > 900 ? 'text-orange-500 font-semibold' : 'text-gray-400'">
                    <span x-text="charCount"></span>/1000
                </span>
            </div>

            <template x-if="message.length > 0">
                <div class="mt-4 p-4 bg-gray-50 border border-gray-100 rounded-xl">
                    <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Vista previa</p>
                    <div class="bg-[#dcf8c6] rounded-lg p-3 shadow-sm max-w-xs ml-auto">
                        <p class="text-xs text-gray-800 leading-relaxed whitespace-pre-wrap" x-text="previewMessage()"></p>
                        <p class="text-right text-[10px] text-gray-400 mt-1">✓✓</p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Las variables se reemplazarán con datos reales al enviar.</p>
                </div>
            </template>
        </div>

        {{-- 5: Programar --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">5. Programar envío <span class="text-gray-400 font-normal">(opcional)</span></h2>
            <p class="text-xs text-gray-500 mb-4">Deja en blanco para enviar manualmente.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y hora de envío</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('scheduled_at') border-red-400 @enderror">
                @error('scheduled_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Driver notice --}}
        @php $waDriver = \App\Models\Setting::get('whatsapp_driver') ?? config('services.whatsapp.driver', 'log'); @endphp
        @if($waDriver === 'log')
            <div class="mb-5 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-yellow-800">Modo desarrollo — Driver: log</p>
                <p class="text-xs text-yellow-700 mt-1">
                    Los mensajes se registrarán en <code class="bg-yellow-100 px-1 rounded">storage/logs/laravel.log</code>.
                    Configura las credenciales Zenvia en <a href="{{ route('admin.providers.index') }}" class="underline">Proveedores</a>.
                </p>
            </div>
        @elseif($waDriver === 'zenvia')
            <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <span class="text-green-500 text-xl">✓</span>
                <div>
                    <p class="text-sm font-semibold text-green-800">Driver activo: Zenvia WhatsApp</p>
                    <p class="text-xs text-green-700">Los mensajes se enviarán a través de la API de Zenvia.</p>
                </div>
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
        message: @json(old('message_template', '')),
        charCount: {{ strlen(old('message_template', '')) }},

        init() {
            const oldId = @json(old('campaign_id', ''));
            if (oldId && this.campaignData[oldId]) {
                this.selectedCampaign = this.campaignData[oldId];
                const oldBatch = @json(old('coupon_batch_id', ''));
                if (oldBatch) {
                    this.selectedBatch = this.selectedCampaign.batches.find(b => String(b.id) === String(oldBatch)) || null;
                }
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
            const start = ta.selectionStart ?? this.message.length;
            const end   = ta.selectionEnd   ?? this.message.length;
            this.message = this.message.substring(0, start) + v + this.message.substring(end);
            this.charCount = this.message.length;
            this.$nextTick(() => { ta.selectionStart = ta.selectionEnd = start + v.length; ta.focus(); });
        },

        previewMessage() {
            const code = this.selectedBatch
                ? (this.selectedBatch.code_type === 'general' ? this.selectedBatch.general_code : (this.selectedBatch.prefix || '') + 'XXXXXXXX')
                : 'CODIGO123';
            const discount = this.selectedBatch
                ? (this.selectedBatch.discount_type === 'percentage'
                    ? this.selectedBatch.discount_value + '%'
                    : '$ ' + Number(this.selectedBatch.discount_value).toLocaleString('es-CO'))
                : '20%';
            return this.message
                .replace(/{name}/g, 'María García')
                .replace(/{code}/g, code)
                .replace(/{discount}/g, discount)
                .replace(/{phone}/g, '3001234567');
        }
    }
}
</script>

@endsection
