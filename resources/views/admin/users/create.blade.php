@extends('layouts.admin')
@section('title', 'Nuevo Usuario')
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.users.index') }}" class="hover:text-gray-600">Usuarios</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Nuevo usuario</span>
</div>

<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nuevo Usuario</h1>
        <p class="text-sm text-gray-500 mt-1">Crea un usuario con acceso al panel de administración.</p>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        {{-- Datos personales --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos del usuario</h2>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-400 @enderror">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                        <select name="document_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Selecciona —</option>
                            @foreach(['CC'=>'Cédula de Ciudadanía','CE'=>'Cédula de Extranjería','NIT'=>'NIT','PP'=>'Pasaporte'] as $val => $label)
                                <option value="{{ $val }}" {{ old('document_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de documento</label>
                    <input type="text" name="document_number" value="{{ old('document_number') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('document_number') border-red-400 @enderror">
                    @error('document_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Rol y estado --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Acceso y rol</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                    <select name="role" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-400 @enderror">
                        <option value="">— Selecciona un rol —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-400 @enderror">
                        <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Descripción de roles --}}
            <div class="mt-4 grid grid-cols-2 gap-2">
                @php
                    $roleDesc = [
                        'super-admin' => ['color'=>'purple','desc'=>'Acceso total sin restricciones'],
                        'admin'       => ['color'=>'blue',  'desc'=>'Gestión completa excepto configuración crítica'],
                        'operador'    => ['color'=>'green', 'desc'=>'Crear y redimir cupones, gestionar clientes'],
                        'analista'    => ['color'=>'yellow','desc'=>'Solo lectura: reportes y estadísticas'],
                    ];
                @endphp
                @foreach($roles as $role)
                    @php $info = $roleDesc[$role->name] ?? ['color'=>'gray','desc'=>'']; @endphp
                    <div class="p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-semibold bg-{{ $info['color'] }}-100 text-{{ $info['color'] }}-700">
                            {{ $role->name }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">{{ $info['desc'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $role->permissions->count() }} permisos</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Crear usuario
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
