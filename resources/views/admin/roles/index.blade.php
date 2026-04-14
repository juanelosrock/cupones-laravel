@extends('layouts.admin')
@section('title', 'Roles y Permisos')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Roles y Permisos</h1>
        <p class="text-sm text-gray-500 mt-0.5">Define qué puede hacer cada tipo de usuario en el sistema</p>
    </div>
    <a href="{{ route('admin.roles.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
        + Nuevo Rol
    </a>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    @php
        $roleColors = [
            'super-admin' => ['bg'=>'purple','label'=>'Super Admin','desc'=>'Acceso completo a todas las funcionalidades y configuraciones del sistema.'],
            'admin'       => ['bg'=>'blue',  'label'=>'Admin',      'desc'=>'Gestión completa de campañas, cupones, clientes y reportes.'],
            'operador'    => ['bg'=>'green', 'label'=>'Operador',   'desc'=>'Crea y redime cupones, gestiona clientes importados.'],
            'analista'    => ['bg'=>'yellow','label'=>'Analista',   'desc'=>'Acceso de solo lectura para generar reportes y estadísticas.'],
        ];
    @endphp

    @foreach($roles as $role)
        @php
            $info = $roleColors[$role->name] ?? ['bg'=>'gray','label'=>$role->name,'desc'=>''];
            $grouped = $role->permissions->groupBy(fn($p) => explode('.', $p->name)[0]);
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-5">
            {{-- Header --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-{{ $info['bg'] }}-100 flex items-center justify-center">
                        <span class="text-lg">
                            @if($role->name === 'super-admin') 🛡️
                            @elseif($role->name === 'admin') ⚙️
                            @elseif($role->name === 'operador') 🎫
                            @else 📊 @endif
                        </span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $role->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $role->users_count }} {{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}</p>
                    </div>
                </div>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-{{ $info['bg'] }}-100 text-{{ $info['bg'] }}-700">
                    {{ $role->permissions->count() }} permisos
                </span>
            </div>

            <p class="text-xs text-gray-500 mb-4">{{ $info['desc'] }}</p>

            {{-- Permisos agrupados por módulo --}}
            <div class="space-y-2">
                @foreach($grouped as $module => $perms)
                    <div class="flex items-start gap-2">
                        <span class="text-xs font-semibold text-gray-500 w-24 flex-shrink-0 mt-0.5 capitalize">{{ $module }}</span>
                        <div class="flex flex-wrap gap-1">
                            @foreach($perms as $perm)
                                @php $action = explode('.', $perm->name)[1] ?? $perm->name; @endphp
                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-600 font-mono">
                                    {{ $action }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between">
                <a href="{{ route('admin.users.index', ['role' => $role->name]) }}"
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Ver usuarios →
                </a>
                @if(!in_array($role->name, ['super-admin','admin','operador','analista']))
                    <span class="text-xs text-gray-400">Rol personalizado</span>
                @endif
            </div>
        </div>
    @endforeach
</div>

@endsection
