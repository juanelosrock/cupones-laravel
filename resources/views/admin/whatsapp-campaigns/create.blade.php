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

    <form method="POST" action="{{ route('admin.whatsapp-campaigns.store') }}"
          enctype="multipart/form-data">
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

        {{-- 2: Destinatarios --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">2. Destinatarios</h2>

            {{-- Toggle fuente --}}
            <div class="grid grid-cols-2 gap-3 mb-5">
                <label class="relative cursor-pointer">
                    <input type="radio" name="recipient_source" value="campaign"
                           x-model="recipientSource" class="sr-only peer">
                    <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 border-gray-200 hover:border-gray-300">
                        <div class="text-xl mb-1">👥</div>
                        <p class="text-sm font-semibold text-gray-800">Clientes de campaña</p>
                        <p class="text-xs text-gray-500 mt-0.5">Usa los clientes vinculados a una campaña existente.</p>
                    </div>
                    <span class="absolute top-2 right-2 hidden peer-checked:flex items-center justify-center">
                        <span class="w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                    </span>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" name="recipient_source" value="csv"
                           x-model="recipientSource" class="sr-only peer">
                    <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-gray-300">
                        <div class="text-xl mb-1">📄</div>
                        <p class="text-sm font-semibold text-gray-800">Subir CSV</p>
                        <p class="text-xs text-gray-500 mt-0.5">Carga un archivo con teléfonos. Hasta 10.000 números.</p>
                    </div>
                    <span class="absolute top-2 right-2 hidden peer-checked:flex items-center justify-center">
                        <span class="w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                    </span>
                </label>
            </div>
            @error('recipient_source')<p class="mb-3 text-xs text-red-600">{{ $message }}</p>@enderror

            {{-- CAMPAIGN MODE --}}
            <div x-show="recipientSource === 'campaign'" x-transition>
                @if($campaigns->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                        <strong>No hay campañas con clientes disponibles.</strong>
                        Importa clientes desde <a href="{{ route('admin.campaigns.index') }}" class="underline">Campañas</a>.
                    </div>
                @else
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Campaña de origen</label>
                        <select name="campaign_id"
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

            {{-- CSV MODE --}}
            <div x-show="recipientSource === 'csv'" x-transition class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-800 space-y-1">
                    <p class="font-semibold">Formato del CSV</p>
                    <ul class="list-disc list-inside space-y-0.5 text-blue-700">
                        <li>Separador: <strong>coma</strong> (<code class="bg-blue-100 px-0.5 rounded">,</code>) o <strong>punto y coma</strong> (<code class="bg-blue-100 px-0.5 rounded">;</code>) — se detecta automáticamente</li>
                        <li>Columnas: <code class="bg-blue-100 px-0.5 rounded">phone</code> (requerida), <code class="bg-blue-100 px-0.5 rounded">name</code> (opcional)</li>
                        <li>Teléfonos colombianos de 10 dígitos se normalizan automáticamente (ej: <code class="bg-blue-100 px-0.5 rounded">3001234567</code> → <code class="bg-blue-100 px-0.5 rounded">573001234567</code>)</li>
                        <li>Duplicados y filas vacías se eliminan automáticamente</li>
                        <li>Máximo 10.000 números por archivo</li>
                    </ul>
                    <div class="mt-2 pt-2 border-t border-blue-200">
                        <p class="font-semibold text-blue-800 mb-1">Ejemplos de formato válido:</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <p class="text-blue-600 mb-0.5">Con encabezado:</p>
                                <code class="block bg-white border border-blue-100 rounded p-1.5 text-[10px] whitespace-pre">phone,name
3001234567,Juan García
3009876543,María López</code>
                            </div>
                            <div>
                                <p class="text-blue-600 mb-0.5">Sin encabezado:</p>
                                <code class="block bg-white border border-blue-100 rounded p-1.5 text-[10px] whitespace-pre">3001234567,Juan García
3009876543,María López
3112223344</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Archivo CSV <span class="text-red-500">*</span></label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-blue-300 transition-colors"
                         @dragover.prevent="$el.classList.add('border-blue-400','bg-blue-50')"
                         @dragleave.prevent="$el.classList.remove('border-blue-400','bg-blue-50')"
                         @drop.prevent="handleCsvDrop($event)">
                        <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt"
                               class="hidden"
                               @change="handleCsvSelect($event)">
                        <template x-if="!csvFileName">
                            <div>
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500 mb-2">Arrastra tu CSV aquí o</p>
                                <button type="button" @click="$refs.csvInput.click()"
                                        class="text-sm bg-white border border-gray-200 hover:border-blue-400 text-gray-700 px-4 py-1.5 rounded-lg transition-colors">
                                    Seleccionar archivo
                                </button>
                            </div>
                        </template>
                        <template x-if="csvFileName">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-medium text-gray-800" x-text="csvFileName"></p>
                                        <p class="text-xs text-gray-400" x-text="csvFileSize"></p>
                                    </div>
                                </div>
                                <button type="button" @click="clearCsv()"
                                        class="text-red-400 hover:text-red-600 text-sm px-2 py-1 rounded">
                                    Quitar
                                </button>
                            </div>
                        </template>
                        <input type="file" x-ref="csvInput" name="csv_file" accept=".csv,.txt"
                               class="hidden" @change="handleCsvSelect($event)">
                    </div>
                    @error('csv_file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
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
        recipientSource: @json(old('recipient_source', 'campaign')),
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
        csvFileName: null,
        csvFileSize: null,

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

        handleCsvSelect(e) {
            const f = e.target.files[0];
            if (f) { this.csvFileName = f.name; this.csvFileSize = this.formatBytes(f.size); }
        },

        handleCsvDrop(e) {
            const f = e.dataTransfer.files[0];
            if (!f) return;
            this.$el.classList.remove('border-blue-400','bg-blue-50');
            if (!f.name.match(/\.(csv|txt)$/i)) { alert('Solo se aceptan archivos .csv o .txt'); return; }
            // Assign to hidden input via DataTransfer
            const dt = new DataTransfer();
            dt.items.add(f);
            this.$refs.csvInput.files = dt.files;
            this.csvFileName = f.name;
            this.csvFileSize = this.formatBytes(f.size);
        },

        clearCsv() {
            this.$refs.csvInput.value = '';
            this.csvFileName = null;
            this.csvFileSize = null;
        },

        formatBytes(b) {
            if (b < 1024) return b + ' B';
            if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
            return (b/1048576).toFixed(1) + ' MB';
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
