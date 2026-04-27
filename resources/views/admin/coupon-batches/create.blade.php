@extends('layouts.admin')
@section('title', 'Nuevo Lote de Cupones')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Nuevo Lote de Cupones</h1>
</div>

<form method="POST" action="{{ route('admin.coupon-batches.store') }}" x-data="{ codeType: 'unique' }">
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Columna principal -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Información General</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del lote *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaña (opcional)</label>
                    <select name="campaign_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sin campaña</option>
                        @foreach($campaigns as $c)
                        @php
                            $statusLabel = ['active'=>'Activa','draft'=>'Borrador','paused'=>'Pausada'][$c->status] ?? $c->status;
                        @endphp
                        <option value="{{ $c->id }}"
                            {{ old('campaign_id', $selectedCampaignId) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} — {{ $statusLabel }}
                        </option>
                        @endforeach
                    </select>
                    @if($campaigns->isEmpty())
                        <p class="mt-1 text-xs text-gray-400">No hay campañas disponibles. <a href="{{ route('admin.campaigns.create') }}" class="text-blue-600 hover:underline">Crear una campaña</a></p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aplica a *</label>
                    <select name="applicable_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['all'=>'Todos','product'=>'Producto','category'=>'Categoría','pos'=>'Punto de Venta','zone'=>'Zona','city'=>'Ciudad'] as $k=>$label)
                        <option value="{{ $k }}" {{ old('applicable_to','all') === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Tipo de Código</h2>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer" :class="codeType==='unique'?'border-blue-500 bg-blue-50':'border-gray-200'">
                    <input type="radio" name="code_type" value="unique" x-model="codeType" class="sr-only">
                    <div>
                        <div class="font-semibold text-sm">🔒 Códigos Únicos</div>
                        <div class="text-xs text-gray-500">Genera N códigos individuales, cada uno de un solo uso</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer" :class="codeType==='general'?'border-blue-500 bg-blue-50':'border-gray-200'">
                    <input type="radio" name="code_type" value="general" x-model="codeType" class="sr-only">
                    <div>
                        <div class="font-semibold text-sm">🌐 Código General</div>
                        <div class="text-xs text-gray-500">Un solo código que puede usar cualquiera (con límites)</div>
                    </div>
                </label>
            </div>

            <div x-show="codeType==='unique'" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prefijo (opcional)</label>
                    <input type="text" name="prefix" value="{{ old('prefix') }}" placeholder="ej: PROMO" maxlength="20"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad a generar *</label>
                    <input type="number" name="quantity" value="{{ old('quantity',100) }}" min="1" max="100000" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div x-show="codeType==='general'" class="mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Código general *</label>
                <input type="text" name="general_code" value="{{ old('general_code') }}" placeholder="ej: DESCUENTO25"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase">
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Descuento</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de descuento *</label>
                    <select name="discount_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="percentage" {{ old('discount_type','percentage')==='percentage'?'selected':'' }}>Porcentaje (%)</option>
                        <option value="fixed" {{ old('discount_type')==='fixed'?'selected':'' }}>Valor fijo ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                    <input type="number" name="discount_value" value="{{ old('discount_value') }}" step="0.01" min="0.01" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- Columna lateral -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Condiciones</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Compra mínima</label>
                    <input type="number" name="min_purchase_amount" value="{{ old('min_purchase_amount',0) }}" min="0" step="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Compra máxima (opcional)</label>
                    <input type="number" name="max_purchase_amount" value="{{ old('max_purchase_amount') }}" min="0" step="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descuento máximo (opcional)</label>
                    <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount') }}" min="0" step="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Sin tope">
                    <p class="text-xs text-gray-400 mt-0.5">Límite en $ al descuento calculado. Aplica sólo a descuentos por porcentaje.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Máx. usos totales</label>
                    <input type="number" name="max_uses_total" value="{{ old('max_uses_total') }}" min="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Sin límite">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Máx. usos por cliente</label>
                    <input type="number" name="max_uses_per_user" value="{{ old('max_uses_per_user') }}" min="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Sin límite">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Máx. usos por día</label>
                    <input type="number" name="max_uses_per_day" value="{{ old('max_uses_per_day') }}" min="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Sin límite">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_combinable" id="combinable" value="1" {{ old('is_combinable') ? 'checked' : '' }} class="rounded">
                    <label for="combinable" class="text-sm text-gray-700">Combinable con otros cupones</label>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm">Crear Lote</button>
            <a href="{{ route('admin.coupon-batches.index') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-lg text-sm">Cancelar</a>
        </div>
    </div>
</div>
</form>
@endsection