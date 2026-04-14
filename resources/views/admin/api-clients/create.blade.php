@extends('layouts.admin')
@section('title', 'Nuevo Cliente API')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.api-clients.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← API Clients</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Nuevo Cliente API</h1>
</div>

<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.api-clients.store') }}" x-data="{ env: 'production', perms: [] }">
    @csrf

    {{-- Identificación --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Identificación</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del cliente <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="150"
                       placeholder="Ej: POS Sr WOK — Producción"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="description" rows="2" maxlength="500" placeholder="Uso o sistema que utilizará estas credenciales..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Entorno --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Entorno <span class="text-red-500">*</span></h2>
        <p class="text-xs text-gray-400 mb-4">El entorno es informativo y queda registrado en los logs.</p>
        <input type="hidden" name="environment" x-model="env">
        <div class="grid grid-cols-2 gap-3">
            <label @click="env='production'"
                   :class="env==='production' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                   class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Producción</p>
                    <p class="text-xs text-gray-500 mt-0.5">Sistema en vivo con datos reales</p>
                </div>
                <span x-show="env==='production'" class="ml-auto w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0">✓</span>
            </label>
            <label @click="env='sandbox'"
                   :class="env==='sandbox' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300'"
                   class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Sandbox</p>
                    <p class="text-xs text-gray-500 mt-0.5">Pruebas e integración</p>
                </div>
                <span x-show="env==='sandbox'" class="ml-auto w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center text-white text-xs flex-shrink-0">✓</span>
            </label>
        </div>
        @error('environment') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Permisos --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Permisos (Scopes) <span class="text-red-500">*</span></h2>
        <p class="text-xs text-gray-400 mb-4">Define qué endpoints puede consumir este cliente.</p>
        @error('permissions') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror
        <div class="space-y-2">
            @php
            $scopeDefs = [
                'validate'  => ['Validar cupones',       'POST /api/v1/coupons/validate — Consulta sin consumir el cupón'],
                'redeem'    => ['Redimir cupones',        'POST /api/v1/coupons/redeem — Consume el cupón y registra la redención'],
                'customers' => ['Gestión de clientes',   'GET/POST /api/v1/customers — Registrar y consultar clientes'],
                'legal'     => ['Documentos legales',     'GET /api/v1/legal/{type} — Consultar T&C vigentes'],
                '*'         => ['Acceso completo (all)', 'Todos los endpoints actuales y futuros'],
            ];
            @endphp
            @foreach($scopeDefs as $scope => [$label, $desc])
            <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="checkbox" name="permissions[]" value="{{ $scope }}"
                       {{ in_array($scope, old('permissions', [])) ? 'checked' : '' }}
                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                    <span class="ml-2 text-[10px] font-mono text-gray-400 bg-gray-100 px-1.5 rounded">{{ $scope }}</span>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Rate limit --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Límite de velocidad</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Máx. requests por minuto <span class="text-red-500">*</span>
                </label>
                <input type="number" name="rate_limit_per_minute"
                       value="{{ old('rate_limit_per_minute', 60) }}" min="1" max="1000"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rate_limit_per_minute') border-red-300 @enderror">
                @error('rate_limit_per_minute') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Recomendado: 30-120 para POS, 300+ para integraciones batch</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento</label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-400">Deja vacío para credenciales sin vencimiento</p>
            </div>
        </div>
    </div>

    {{-- IP Whitelist --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Whitelist de IPs</h2>
        <p class="text-xs text-gray-400 mb-3">Restringe el acceso a IPs específicas. Deja vacío para permitir cualquier IP.</p>
        <textarea name="allowed_ips" rows="3"
                  placeholder="192.168.1.100&#10;10.0.0.0/24&#10;203.0.113.45"
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('allowed_ips') }}</textarea>
        <p class="mt-1 text-xs text-gray-400">Una IP por línea. Soporta CIDR (ej: 10.0.0.0/24) solo como referencia visual — la validación es por IP exacta.</p>
    </div>

    {{-- Acciones --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
            Generar Credenciales
        </button>
        <a href="{{ route('admin.api-clients.index') }}"
           class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
            Cancelar
        </a>
    </div>
</form>
</div>
@endsection
