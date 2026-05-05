@extends('layouts.admin')
@section('title', 'Clientes')
@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.customers.import') }}"
           class="flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Importar CSV
        </a>
        <a href="{{ route('admin.customers.create') }}"
           class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Cliente
        </a>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
    {{ session('success') }}
</div>
@endif
@if(session('import_errors'))
<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
    <p class="text-sm font-semibold text-amber-800 mb-1">Filas con error (primeras 20):</p>
    @foreach(session('import_errors') as $err)
    <p class="text-xs text-amber-700">{{ $err }}</p>
    @endforeach
</div>
@endif

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Total clientes</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Activos</p>
        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Con datos autorizados</p>
        <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['accepted']) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">
            {{ $stats['total'] > 0 ? round($stats['accepted'] / $stats['total'] * 100) : 0 }}% del total
        </p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Bloqueados</p>
        <p class="text-2xl font-bold text-red-500">{{ number_format($stats['blocked']) }}</p>
    </div>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-xl shadow-sm">

    {{-- Filtros --}}
    <form method="GET" class="p-4 border-b flex gap-3 flex-wrap items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nombre, teléfono, documento, email..."
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Estado</label>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="active"       {{ request('status') === 'active'       ? 'selected' : '' }}>Activos</option>
                <option value="blocked"      {{ request('status') === 'blocked'      ? 'selected' : '' }}>Bloqueados</option>
                <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Desuscritos</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Datos</label>
            <select name="accepted" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="yes" {{ request('accepted') === 'yes' ? 'selected' : '' }}>Autorizados</option>
                <option value="no"  {{ request('accepted') === 'no'  ? 'selected' : '' }}>Pendientes</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Desde</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Hasta</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
        @if(request()->hasAny(['search', 'status', 'accepted', 'date_from', 'date_to']))
        <a href="{{ route('admin.customers.index') }}" class="text-gray-400 hover:text-gray-600 text-sm py-2">Limpiar</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-left text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Celular</th>
                    <th class="px-4 py-3">Ciudad</th>
                    <th class="px-4 py-3">Datos pers.</th>
                    <th class="px-4 py-3">Canal</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Registro</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($customers as $c)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-900">{{ $c->name }} {{ $c->lastname }}</p>
                    @if($c->document_number)
                    <p class="text-xs text-gray-400">{{ $c->document_type }} {{ $c->document_number }}</p>
                    @endif
                    @if($c->email)
                    <p class="text-xs text-gray-400">{{ $c->email }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 font-mono text-xs">{{ $c->phone }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $c->city?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    @if($c->data_treatment_accepted)
                    <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Autorizado
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                        Pendiente
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs text-gray-500 uppercase">{{ $c->created_via }}</span>
                </td>
                <td class="px-4 py-3">
                    @php $sc = ['active' => 'green', 'blocked' => 'red', 'unsubscribed' => 'gray'] @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full
                        bg-{{ $sc[$c->status] ?? 'gray' }}-100 text-{{ $sc[$c->status] ?? 'gray' }}-700">
                        {{ ['active' => 'Activo', 'blocked' => 'Bloqueado', 'unsubscribed' => 'Desuscrito'][$c->status] ?? $c->status }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $c->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.customers.show', $c) }}"
                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">Ver</a>
                        <a href="{{ route('admin.customers.edit', $c) }}"
                           class="text-gray-500 hover:text-gray-700 text-xs font-medium">Editar</a>
                        @if($c->status === 'active')
                        <form method="POST" action="{{ route('admin.customers.block', $c) }}"
                              onsubmit="return confirm('¿Bloquear este cliente?')">
                            @csrf
                            <button class="text-red-500 hover:text-red-700 text-xs">Bloquear</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.customers.unblock', $c) }}">
                            @csrf
                            <button class="text-green-600 hover:text-green-800 text-xs">Desbloquear</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    No hay clientes.
                    <a href="{{ route('admin.customers.create') }}" class="text-blue-600 hover:underline ml-1">Crea el primero</a>
                    o
                    <a href="{{ route('admin.customers.import') }}" class="text-blue-600 hover:underline">importa desde CSV</a>.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t">
        {{ $customers->links() }}
    </div>
</div>
@endsection
