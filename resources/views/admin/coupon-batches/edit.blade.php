@extends('layouts.admin')
@section('title', 'Editar Lote')
@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.coupon-batches.show', $couponBatch) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        ← {{ $couponBatch->name }}
    </a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Editar Lote</h1>
</div>

<form id="form-update-batch" method="POST" action="{{ route('admin.coupon-batches.update', $couponBatch) }}">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Info general --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Información general</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del lote <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $couponBatch->name) }}" required maxlength="150"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" rows="2"
                                  class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $couponBatch->description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Campaña</label>
                        <select name="campaign_id" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Sin campaña</option>
                            @foreach($campaigns as $c)
                            @php $statusLabel = ['active'=>'Activa','draft'=>'Borrador','paused'=>'Pausada'][$c->status] ?? $c->status; @endphp
                            <option value="{{ $c->id }}" {{ old('campaign_id', $couponBatch->campaign_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} — {{ $statusLabel }}
                            </option>
                            @endforeach
                        </select>
                        @error('campaign_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @if($campaigns->isEmpty())
                            <p class="mt-1 text-xs text-gray-400">No hay campañas disponibles. <a href="{{ route('admin.campaigns.create') }}" class="text-blue-600 hover:underline">Crear una</a></p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Aplica a <span class="text-red-500">*</span></label>
                        <select name="applicable_to" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['all'=>'Todos','product'=>'Producto','category'=>'Categoría','pos'=>'Punto de Venta','zone'=>'Zona','city'=>'Ciudad'] as $k => $label)
                            <option value="{{ $k }}" {{ old('applicable_to', $couponBatch->applicable_to) === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Tipo de código (solo lectura) --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                <h2 class="font-semibold text-gray-700 mb-2 text-sm">Tipo de código <span class="text-xs text-gray-400 font-normal">(no editable tras la creación)</span></h2>
                <div class="flex items-center gap-3">
                    <span class="text-xl">{{ $couponBatch->code_type === 'unique' ? '🔒' : '🌐' }}</span>
                    <div>
                        <p class="text-sm font-medium text-gray-700">
                            {{ $couponBatch->code_type === 'unique' ? 'Códigos únicos' : 'Código general' }}
                        </p>
                        @if($couponBatch->code_type === 'unique')
                            <p class="text-xs text-gray-500">
                                {{ number_format($couponBatch->coupons()->count()) }} códigos generados
                                @if($couponBatch->prefix) · Prefijo: <strong>{{ $couponBatch->prefix }}</strong> @endif
                            </p>
                        @else
                            <p class="text-xs text-gray-500">Código: <strong class="font-mono">{{ $couponBatch->general_code }}</strong></p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Descuento --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Descuento</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select name="discount_type" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="percentage" {{ old('discount_type', $couponBatch->discount_type) === 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                            <option value="fixed"      {{ old('discount_type', $couponBatch->discount_type) === 'fixed'      ? 'selected' : '' }}>Valor fijo ($)</option>
                        </select>
                        @error('discount_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor <span class="text-red-500">*</span></label>
                        <input type="number" name="discount_value" value="{{ old('discount_value', $couponBatch->discount_value) }}"
                               step="0.01" min="0.01" required
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('discount_value') border-red-300 @enderror">
                        @error('discount_value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Condiciones --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Condiciones de uso</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', $couponBatch->start_date->format('Y-m-d')) }}" required
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-300 @enderror">
                        @error('start_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', $couponBatch->end_date->format('Y-m-d')) }}" required
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-300 @enderror">
                        @error('end_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compra mínima (COP)</label>
                        <input type="number" name="min_purchase_amount" value="{{ old('min_purchase_amount', $couponBatch->min_purchase_amount) }}"
                               min="0" step="100"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compra máxima (COP)</label>
                        <input type="number" name="max_purchase_amount" value="{{ old('max_purchase_amount', $couponBatch->max_purchase_amount) }}"
                               min="0" step="100" placeholder="Sin límite"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Máx. usos totales</label>
                        <input type="number" name="max_uses_total" value="{{ old('max_uses_total', $couponBatch->max_uses_total) }}"
                               min="1" placeholder="Sin límite"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Máx. usos por cliente</label>
                        <input type="number" name="max_uses_per_user" value="{{ old('max_uses_per_user', $couponBatch->max_uses_per_user) }}"
                               min="1" placeholder="Sin límite"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Máx. usos por día</label>
                        <input type="number" name="max_uses_per_day" value="{{ old('max_uses_per_day', $couponBatch->max_uses_per_day) }}"
                               min="1" placeholder="Sin límite"
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_combinable" id="combinable" value="1"
                               {{ old('is_combinable', $couponBatch->is_combinable) ? 'checked' : '' }}
                               class="rounded border-gray-300">
                        <label for="combinable" class="text-sm text-gray-700">Combinable con otros cupones</label>
                    </div>
                </div>
            </div>

        </div>

        {{-- Columna lateral --}}
        <div class="space-y-5">

            {{-- Estado --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Estado</h2>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="draft"     {{ old('status', $couponBatch->status) === 'draft'     ? 'selected' : '' }}>Borrador</option>
                    <option value="active"    {{ old('status', $couponBatch->status) === 'active'    ? 'selected' : '' }}>Activo</option>
                    <option value="paused"    {{ old('status', $couponBatch->status) === 'paused'    ? 'selected' : '' }}>Pausado</option>
                    <option value="expired"   {{ old('status', $couponBatch->status) === 'expired'   ? 'selected' : '' }}>Expirado</option>
                    <option value="cancelled" {{ old('status', $couponBatch->status) === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Meta info --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-xs text-gray-500 space-y-2">
                <div class="flex justify-between">
                    <span>Creado por</span>
                    <span class="font-medium text-gray-700">{{ $couponBatch->createdBy?->name ?? 'Sistema' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Creado</span>
                    <span class="font-medium text-gray-700">{{ $couponBatch->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Modificado</span>
                    <span class="font-medium text-gray-700">{{ $couponBatch->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex flex-col gap-3">
                <button type="submit" form="form-update-batch"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Guardar Cambios
                </button>
                <a href="{{ route('admin.coupon-batches.show', $couponBatch) }}"
                   class="w-full text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    Cancelar
                </a>
            </div>

        </div>
    </div>
</form>

@endsection
