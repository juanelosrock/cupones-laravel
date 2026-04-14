@extends('layouts.admin')
@section('title', 'Editar — ' . $user->name)
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.users.index') }}" class="hover:text-gray-600">Usuarios</a>
    <span>/</span>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $user->name }}</span>
</div>

<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Usuario</h1>
            <p class="text-sm text-gray-500 mt-1">Modificando: <strong>{{ $user->email }}</strong></p>
        </div>
        @if($user->id !== auth()->id())
            <form id="form-delete-user" method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('¿Eliminar al usuario {{ addslashes($user->name) }}? Esta acción no se puede deshacer.')">
                @csrf @method('DELETE')
            </form>
            <button form="form-delete-user"
                    class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Eliminar usuario
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
            <span class="text-green-500">✓</span> {{ session('success') }}
        </div>
    @endif

    <form id="form-update-user" method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf @method('PUT')

        {{-- Datos personales --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Datos del usuario</h2>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-400 @enderror">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                        <select name="document_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Selecciona —</option>
                            @foreach(['CC'=>'Cédula de Ciudadanía','CE'=>'Cédula de Extranjería','NIT'=>'NIT','PP'=>'Pasaporte'] as $val => $label)
                                <option value="{{ $val }}" {{ old('document_type', $user->document_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de documento</label>
                    <input type="text" name="document_number" value="{{ old('document_number', $user->document_number) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Cambiar contraseña --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5" x-data="{ changing: false }">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Contraseña</h2>
                <button type="button" @click="changing = !changing"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    <span x-show="!changing">Cambiar contraseña</span>
                    <span x-show="changing">Cancelar</span>
                </button>
            </div>

            <div x-show="!changing" class="mt-3">
                <p class="text-sm text-gray-400">••••••••••••</p>
                <p class="text-xs text-gray-400 mt-1">Haz clic en "Cambiar contraseña" para actualizarla.</p>
            </div>

            <div x-show="changing" class="mt-3 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                    <input type="password" name="password" minlength="8" :required="changing"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" :required="changing"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Rol y estado --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Acceso y rol</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                    <select name="role" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-400 @enderror"
                            @if($user->id === auth()->id()) disabled @endif>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->roles->first()?->name }}">
                        <p class="text-xs text-gray-400 mt-1">No puedes cambiar tu propio rol.</p>
                    @endif
                    @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado <span class="text-red-500">*</span></label>
                    <select name="status" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-400 @enderror"
                            @if($user->id === auth()->id()) disabled @endif>
                        <option value="active"   {{ old('status', $user->status) === 'active'   ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        <option value="blocked"  {{ old('status', $user->status) === 'blocked'  ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="status" value="{{ $user->status }}">
                        <p class="text-xs text-gray-400 mt-1">No puedes cambiar tu propio estado.</p>
                    @endif
                    @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-500">
                Creado el {{ $user->created_at->format('d/m/Y H:i') }}
                · Última actualización {{ $user->updated_at->diffForHumans() }}
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" form="form-update-user"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Guardar cambios
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
