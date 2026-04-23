@extends('layouts.admin')
@section('title', 'Nueva Campaña de Email')
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.email-campaigns.index') }}" class="hover:text-gray-600">Campañas Email</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Nueva campaña</span>
</div>

<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Nueva Campaña de Email</h1>

    <form method="POST" action="{{ route('admin.email-campaigns.store') }}"
          x-data="emailCampaignForm()" @submit.prevent="submitForm">
        @csrf

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Nombre --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Información general</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre de la campaña *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none"
                           placeholder="Ej: Promo Mayo 2026 — Email">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campaña de origen *</label>
                    <select name="campaign_id" x-model="selectedCampaign" @change="onCampaignChange" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                        <option value="">— Seleccionar campaña —</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}" {{ old('campaign_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->campaign_customers_count }} clientes)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Solo se incluirán clientes activos con email registrado.</p>
                </div>

                <div x-show="selectedCampaign && batches.length > 0">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lote de cupones (opcional)</label>
                    <select name="coupon_batch_id" x-model="selectedBatch"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                        <option value="">— Sin cupón —</option>
                        <template x-for="b in batches" :key="b.id">
                            <option :value="b.id"
                                    :selected="b.id == '{{ old('coupon_batch_id') }}'">
                                <span x-text="b.label"></span>
                                <span x-text="b.batch_status !== 'active' ? ' — PAUSADO' : ''"></span>
                            </option>
                        </template>
                    </select>
                    <p x-show="selectedBatch && getBatch()?.batch_status !== 'active'"
                       class="text-xs text-yellow-600 mt-1">⚠️ Este lote está pausado — actívalo antes de enviar.</p>
                </div>
            </div>
        </div>

        {{-- Remitente --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Remitente</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del remitente *</label>
                    <input type="text" name="from_name" value="{{ old('from_name', $defaultFromName) }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none"
                           placeholder="CuponesHub">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Correo remitente *</label>
                    <input type="email" name="from_email" value="{{ old('from_email', $defaultFrom) }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none"
                           placeholder="noreply@tudominio.com">
                    <p class="text-xs text-gray-400 mt-1">Debe estar verificado en Zenvia.</p>
                </div>
            </div>
        </div>

        {{-- Contenido del email --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Contenido del email</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Asunto *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none"
                           placeholder="Ej: Tu cupón de descuento {discount} está listo 🎁">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cuerpo del email (HTML) *</label>
                    <textarea name="message_template" rows="12" required
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none resize-y"
                              placeholder="<p>Hola {name},</p>
<p>Tu código de descuento es: <strong>{code}</strong></p>
<p>Descuento: {discount}</p>">{{ old('message_template') }}</textarea>
                    <div class="mt-2 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                        <p class="text-xs font-semibold text-blue-700 mb-1">Variables disponibles:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['{name}'=>'Nombre del cliente','{email}'=>'Email','{code}'=>'Código de cupón','{discount}'=>'Valor del descuento'] as $var => $desc)
                                <span class="text-xs bg-white border border-blue-200 text-blue-700 px-2 py-0.5 rounded font-mono"
                                      title="{{ $desc }}">{{ $var }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Programación --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Programación (opcional)</h2>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Enviar en fecha/hora específica</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                <p class="text-xs text-gray-400 mt-1">Si dejas vacío, la campaña quedará en estado borrador y podrás enviarla manualmente.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Crear campaña
            </button>
            <a href="{{ route('admin.email-campaigns.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700">Cancelar</a>
        </div>
    </form>
</div>

<script>
const campaignData = @json($campaignData);

function emailCampaignForm() {
    return {
        selectedCampaign: '{{ old('campaign_id') }}',
        selectedBatch: '{{ old('coupon_batch_id') }}',
        batches: [],

        onCampaignChange() {
            const data = campaignData[this.selectedCampaign];
            this.batches = data ? data.batches : [];
            this.selectedBatch = '';
        },

        getBatch() {
            return this.batches.find(b => b.id == this.selectedBatch) || null;
        },

        submitForm() {
            this.$el.submit();
        },

        init() {
            if (this.selectedCampaign) {
                const data = campaignData[this.selectedCampaign];
                this.batches = data ? data.batches : [];
            }
        }
    };
}
</script>
@endsection
