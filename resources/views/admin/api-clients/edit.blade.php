@extends('layouts.admin')
@section('title', 'Editar ' . $apiClient->name)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.api-clients.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← API Clients</a>
    <span class="text-gray-300">/</span>
    <a href="{{ route('admin.api-clients.show', $apiClient) }}" class="text-gray-400 hover:text-gray-600 transition-colors">{{ $apiClient->name }}</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Editar configuración</h1>
</div>

<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.api-clients.update', $apiClient) }}">
    @csrf
    @method('PUT')

    {{-- Identificación --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Identificación</h2>

        {{-- Entorno (read-only) --}}
        <div class="mb-4 p-3 bg-gray-50 border border-gray-100 rounded-lg flex items-center gap-3">
            @if(($apiClient->environment ?? 'production') === 'sandbox')
            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Sandbox</span>
            @else
            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Producción</span>
            @endif
            <p class="text-xs text-gray-500">El entorno no se puede cambiar después de la creación.</p>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del cliente <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $apiClient->name) }}" required maxlength="150"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="description" rows="2" maxlength="500"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $apiClient->description) }}</textarea>
            </div>
        </div>
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
            $currentPerms = old('permissions', $apiClient->permissions ?? []);
            @endphp
            @foreach($scopeDefs as $scope => [$label, $desc])
            <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="checkbox" name="permissions[]" value="{{ $scope }}"
                       {{ in_array($scope, $currentPerms) ? 'checked' : '' }}
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

    {{-- Rate limit + vencimiento --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Límite de velocidad</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Máx. requests por minuto <span class="text-red-500">*</span>
                </label>
                <input type="number" name="rate_limit_per_minute"
                       value="{{ old('rate_limit_per_minute', $apiClient->rate_limit_per_minute) }}"
                       min="1" max="1000"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rate_limit_per_minute') border-red-300 @enderror">
                @error('rate_limit_per_minute') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Recomendado: 30–120 para POS, 300+ para batch</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento</label>
                <input type="date" name="expires_at"
                       value="{{ old('expires_at', $apiClient->expires_at?->format('Y-m-d')) }}"
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
                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('allowed_ips', implode("\n", $apiClient->allowed_ips ?? [])) }}</textarea>
        <p class="mt-1 text-xs text-gray-400">Una IP por línea.</p>
    </div>

    {{-- Acciones --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
            Guardar cambios
        </button>
        <a href="{{ route('admin.api-clients.show', $apiClient) }}"
           class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
            Cancelar
        </a>
    </div>
</form>
</div>
@endsection
