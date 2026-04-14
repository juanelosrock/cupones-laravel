@extends('layouts.admin')
@section('title', 'Usuarios')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
        <p class="text-sm text-gray-500 mt-0.5">Gestiona los usuarios del panel de administración</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        + Nuevo Usuario
    </a>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif

{{-- Filtros --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nombre o email..."
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Rol</label>
            <select name="role" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Limpiar</a>
            @endif
        </div>
    </form>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($users->isEmpty())
        <div class="text-center py-16">
            <div class="text-5xl mb-4">👤</div>
            <p class="text-gray-500 font-medium">No se encontraron usuarios</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Usuario</th>
                    <th class="text-left px-4 py-3">Documento</th>
                    <th class="text-left px-4 py-3">Teléfono</th>
                    <th class="text-left px-4 py-3">Rol</th>
                    <th class="text-left px-4 py-3">Estado</th>
                    <th class="text-left px-4 py-3">Creado</th>
                    <th class="text-right px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        @if($user->document_number)
                            {{ $user->document_type }} {{ $user->document_number }}
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs font-mono text-gray-600">
                        {{ $user->phone ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @foreach($user->roles as $role)
                            @php
                                $roleColors = [
                                    'super-admin' => 'bg-purple-100 text-purple-700',
                                    'admin'       => 'bg-blue-100 text-blue-700',
                                    'operador'    => 'bg-green-100 text-green-700',
                                    'analista'    => 'bg-yellow-100 text-yellow-700',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3">
                        @if($user->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Activo
                            </span>
                        @elseif($user->status === 'inactive')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactivo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Bloqueado
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">Editar</a>
                            @if($user->id !== auth()->id())
                                <span class="text-gray-200">|</span>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('¿Eliminar al usuario {{ addslashes($user->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ number_format($users->total()) }} usuarios
            </p>
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
