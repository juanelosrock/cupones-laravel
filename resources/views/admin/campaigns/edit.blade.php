@extends('layouts.admin')
@section('title', 'Editar Campaña')
@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        ← {{ $campaign->name }}
    </a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Editar Campaña</h1>
</div>

<form id="form-update-campaign" method="POST" action="{{ route('admin.campaigns.update', $campaign) }}" x-data="campaignForm()">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Basic info --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Información básica</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required maxlength="150"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" rows="3" maxlength="1000"
                                  class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('description') border-red-300 @enderror">{{ old('description', $campaign->description) }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Type --}}
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
                        <input type="date" name="start_date" value="{{ old('start_date', $campaign->start_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-300 @enderror">
                        @error('start_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de fin</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $campaign->end_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-300 @enderror">
                        @error('end_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Geografía --}}
            @include('admin.campaigns._location_selector', [
                'zones'         => $zones,
                'pointsOfSale'  => $pointsOfSale,
                'selectedZones' => old('zone_ids', $selectedZones),
                'selectedPOS'   => old('pos_ids', $selectedPOS),
            ])

        </div>

        {{-- Side column --}}
        <div class="space-y-5">

            {{-- Status & Budget --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Configuración</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado <span class="text-red-500">*</span></label>
                        <select name="status" required
                                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="draft"     {{ old('status', $campaign->status) === 'draft'     ? 'selected' : '' }}>Borrador</option>
                            <option value="active"    {{ old('status', $campaign->status) === 'active'    ? 'selected' : '' }}>Activa</option>
                            <option value="paused"    {{ old('status', $campaign->status) === 'paused'    ? 'selected' : '' }}>Pausada</option>
                            <option value="finished"  {{ old('status', $campaign->status) === 'finished'  ? 'selected' : '' }}>Finalizada</option>
                            <option value="cancelled" {{ old('status', $campaign->status) === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Presupuesto (COP)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                            <input type="number" name="budget" value="{{ old('budget', $campaign->budget) }}" min="0" step="1000"
                                   class="w-full border border-gray-200 rounded-lg pl-7 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('budget') border-red-300 @enderror">
                        </div>
                        @error('budget') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Meta info --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-xs text-gray-500 space-y-2">
                <div class="flex justify-between">
                    <span>Creada por</span>
                    <span class="font-medium text-gray-700">{{ $campaign->createdBy?->name ?? 'Sistema' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Creada</span>
                    <span class="font-medium text-gray-700">{{ $campaign->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Última modificación</span>
                    <span class="font-medium text-gray-700">{{ $campaign->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3">
                <button type="submit" form="form-update-campaign"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Guardar Cambios
                </button>
                <a href="{{ route('admin.campaigns.show', $campaign) }}"
                   class="w-full text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    Cancelar
                </a>
                <div class="border-t border-gray-100 pt-3">
                    <button type="submit" form="form-delete-campaign"
                            class="w-full text-center text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Eliminar campaña
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>

{{-- Form eliminar — fuera del form principal para evitar anidamiento inválido --}}
<form id="form-delete-campaign"
      method="POST"
      action="{{ route('admin.campaigns.destroy', $campaign) }}"
      onsubmit="return confirm('¿Eliminar la campaña «{{ addslashes($campaign->name) }}»? Esta acción no se puede deshacer.')">
    @csrf @method('DELETE')
</form>

<script>
function campaignForm() {
    return {
        type: '{{ old('type', $campaign->type) }}',
    }
}
</script>
@endsection
