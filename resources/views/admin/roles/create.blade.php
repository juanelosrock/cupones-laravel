@extends('layouts.admin')
@section('title', 'Nuevo Rol')
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.roles.index') }}" class="hover:text-gray-600">Roles</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Nuevo rol</span>
</div>

<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nuevo Rol</h1>
        <p class="text-sm text-gray-500 mt-1">Define un rol personalizado con permisos específicos.</p>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}" x-data="{ selected: {{ old('permissions') ? json_encode(old('permissions')) : '[]' }} }">
        @csrf

        {{-- Nombre --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Nombre del rol</h2>
            <input type="text" name="name" value="{{ old('name') }}" required
                   placeholder="Ej: supervisor, soporte, reportes..."
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <p class="text-xs text-gray-400 mt-2">Usa minúsculas y guiones. Ejemplo: <code class="bg-gray-100 px-1 rounded">soporte-ventas</code></p>
        </div>

        {{-- Permisos agrupados por módulo --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">Permisos</h2>
                <div class="flex gap-2">
                    <button type="button"
                            @click="selected = {{ json_encode(\Spatie\Permission\Models\Permission::pluck('name')->toArray()) }}"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Seleccionar todos
                    </button>
                    <span class="text-gray-300">|</span>
                    <button type="button" @click="selected = []"
                            class="text-xs text-gray-500 hover:text-gray-700 font-medium">
                        Limpiar
                    </button>
                </div>
            </div>

            @php
                $moduleIcons = [
                    'users'       => '👤',
                    'roles'       => '🛡️',
                    'campaigns'   => '📣',
                    'coupons'     => '🎫',
                    'customers'   => '👥',
                    'geography'   => '🗺️',
                    'legal'       => '📋',
                    'sms'         => '📱',
                    'api_clients' => '🔑',
                    'audit'       => '📝',
                    'dashboard'   => '📊',
                ];
                $moduleLabels = [
                    'users'       => 'Usuarios',
                    'roles'       => 'Roles',
                    'campaigns'   => 'Campañas',
                    'coupons'     => 'Cupones',
                    'customers'   => 'Clientes',
                    'geography'   => 'Geografía',
                    'legal'       => 'Documentos Legales',
                    'sms'         => 'Campañas SMS',
                    'api_clients' => 'API Clients',
                    'audit'       => 'Auditoría',
                    'dashboard'   => 'Dashboard',
                ];
                $actionLabels = [
                    'view'    => 'Ver',
                    'create'  => 'Crear',
                    'edit'    => 'Editar',
                    'delete'  => 'Eliminar',
                    'manage'  => 'Administrar',
                    'publish' => 'Publicar',
                    'send'    => 'Enviar',
                    'revoke'  => 'Revocar',
                    'redeem'  => 'Redimir',
                    'reverse' => 'Reversar',
                    'cancel'  => 'Cancelar',
                ];
            @endphp

            <div class="space-y-4">
                @foreach($permissions as $module => $perms)
                    @php
                        $permNames = $perms->pluck('name')->toArray();
                    @endphp
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        {{-- Module header --}}
                        <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span>{{ $moduleIcons[$module] ?? '🔧' }}</span>
                                <span class="text-sm font-semibold text-gray-700">{{ $moduleLabels[$module] ?? ucfirst($module) }}</span>
                                <span class="text-xs text-gray-400">({{ $perms->count() }})</span>
                            </div>
                            <button type="button"
                                    @click="
                                        const all = {{ json_encode($permNames) }};
                                        const hasAll = all.every(p => selected.includes(p));
                                        if (hasAll) { selected = selected.filter(p => !all.includes(p)); }
                                        else { selected = [...new Set([...selected, ...all])]; }
                                    "
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                <span x-text="{{ json_encode($permNames) }}.every(p => selected.includes(p)) ? 'Quitar todos' : 'Todos'"></span>
                            </button>
                        </div>

                        {{-- Permissions --}}
                        <div class="p-3 flex flex-wrap gap-2">
                            @foreach($perms as $perm)
                                @php $action = explode('.', $perm->name)[1] ?? $perm->name; @endphp
                                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $perm->name }}"
                                           x-model="selected"
                                           class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-xs text-gray-700 font-medium">{{ $actionLabels[$action] ?? $action }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @error('permissions')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-between">
                <p class="text-xs text-blue-700">
                    Permisos seleccionados: <strong x-text="selected.length"></strong>
                </p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Crear rol
            </button>
            <a href="{{ route('admin.roles.index') }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
