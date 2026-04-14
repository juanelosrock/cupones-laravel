@extends('layouts.admin')
@section('title', 'Nueva Campaña')
@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.campaigns.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        ← Campañas
    </a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Nueva Campaña</h1>
</div>

<form method="POST" action="{{ route('admin.campaigns.store') }}" x-data="campaignForm()">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Basic info --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Información básica</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de la campaña <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="150"
                               placeholder="Ej: Descuento Día de la Madre 2026"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" rows="3" maxlength="1000"
                                  placeholder="Describe el objetivo y alcance de la campaña..."
                                  class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Type selector --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-1">Tipo de campaña <span class="text-red-500">*</span></h2>
                <p class="text-xs text-gray-400 mb-4">Define la naturaleza y canal principal de la campaña</p>

                <input type="hidden" name="type" x-model="type">
                @error('type') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="grid grid-cols-2 gap-3">
                    @php
                    $types = [
                        ['value'=>'general',      'icon'=>'🎯', 'label'=>'General',       'desc'=>'Cupones de descuento estándar sin canal específico'],
                        ['value'=>'sms',          'icon'=>'📱', 'label'=>'SMS',            'desc'=>'Distribuidos por mensajes de texto a clientes'],
                        ['value'=>'product',      'icon'=>'📦', 'label'=>'Producto',       'desc'=>'Aplicados a productos o categorías específicas'],
                        ['value'=>'activation',   'icon'=>'⚡', 'label'=>'Activación',     'desc'=>'Para activar o reactivar clientes inactivos'],
                        ['value'=>'autorizacion', 'icon'=>'📋', 'label'=>'Autorización',   'desc'=>'Solo clientes sin autorización de datos — excluye quienes ya la tienen'],
                    ];
                    @endphp
                    @foreach($types as $t)
                    <label @click="type = '{{ $t['value'] }}'"
                           class="relative flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                           :class="type === '{{ $t['value'] }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                        <span class="text-2xl">{{ $t['icon'] }}</span>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">{{ $t['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $t['desc'] }}</p>
                        </div>
                        <span x-show="type === '{{ $t['value'] }}'"
                              class="absolute top-3 right-3 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs">✓</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Dates --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Vigencia</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de inicio</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-300 @enderror">
                        @error('start_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de fin</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-300 @enderror">
                        @error('end_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-400">Deja vacío si la campaña no tiene límite de tiempo definido.</p>
            </div>

            {{-- Geografía --}}
            @include('admin.campaigns._location_selector', [
                'zones'         => $zones,
                'pointsOfSale'  => $pointsOfSale,
                'selectedZones' => old('zone_ids', []),
                'selectedPOS'   => old('pos_ids', []),
            ])

        </div>

        {{-- Side column --}}
        <div class="space-y-5">

            {{-- Status & Budget --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Configuración</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado inicial <span class="text-red-500">*</span></label>
                        <select name="status" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="draft"  {{ old('status','draft') === 'draft'  ? 'selected' : '' }}>Borrador</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Activa</option>
                            <option value="paused" {{ old('status') === 'paused' ? 'selected' : '' }}>Pausada</option>
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1.5 text-xs text-gray-400">En borrador puedes seguir configurando antes de lanzar.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Presupuesto (COP)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                            <input type="number" name="budget" value="{{ old('budget') }}" min="0" step="1000"
                                   placeholder="0"
                                   class="w-full border border-gray-200 rounded-lg pl-7 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('budget') border-red-300 @enderror">
                        </div>
                        @error('budget') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1.5 text-xs text-gray-400">Opcional. Permite rastrear el % de presupuesto utilizado.</p>
                    </div>
                </div>
            </div>

            {{-- Summary preview --}}
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-blue-800 mb-3">Resumen</h3>
                <div class="space-y-2 text-xs text-blue-700">
                    <div class="flex justify-between">
                        <span class="text-blue-500">Tipo</span>
                        <span x-text="typeLabel()" class="font-medium capitalize"></span>
                    </div>
                </div>
                <p class="mt-4 text-xs text-blue-500">
                    Después de crear la campaña podrás agregar lotes de cupones y configurar restricciones.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Crear Campaña
                </button>
                <a href="{{ route('admin.campaigns.index') }}"
                   class="w-full text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    Cancelar
                </a>
            </div>

        </div>
    </div>
</form>

<script>
function campaignForm() {
    return {
        type: '{{ old('type', 'general') }}',
        typeLabel() {
            const labels = { general: 'General', sms: 'SMS', product: 'Producto', activation: 'Activación', autorizacion: 'Autorización' };
            return labels[this.type] || '—';
        }
    }
}
</script>
@endsection
