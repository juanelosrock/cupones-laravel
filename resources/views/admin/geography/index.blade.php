@extends('layouts.admin')
@section('title', 'Geografía')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Geografía</h1>
        <p class="text-sm text-gray-500 mt-1">Países, departamentos, ciudades, zonas y puntos de venta.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 flex items-center gap-2">
        <span class="text-green-500">✓</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @foreach([
        ['label' => 'Países',          'value' => $stats['countries'],   'color' => 'text-blue-600'],
        ['label' => 'Departamentos',   'value' => $stats['departments'], 'color' => 'text-indigo-600'],
        ['label' => 'Ciudades',        'value' => $stats['cities'],      'color' => 'text-purple-600'],
        ['label' => 'Zonas',           'value' => $stats['zones'],       'color' => 'text-pink-600'],
        ['label' => 'PDV total',       'value' => $stats['pos'],         'color' => 'text-gray-700'],
        ['label' => 'PDV activos',     'value' => $stats['pos_active'],  'color' => 'text-green-600'],
    ] as $kpi)
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $kpi['label'] }}</p>
        <p class="text-2xl font-bold {{ $kpi['color'] }} mt-1">{{ number_format($kpi['value']) }}</p>
    </div>
    @endforeach
</div>

{{-- Tabs --}}
<div x-data="{ tab: '{{ $errors->hasBag('city') ? 'cities' : ($errors->hasBag('zone') ? 'zones' : ($errors->hasBag('pos') || $errors->any() ? 'pos' : 'tree')) }}' }"
     class="space-y-5">

    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
        @foreach([
            ['key' => 'tree',   'label' => 'Árbol geográfico'],
            ['key' => 'cities', 'label' => 'Ciudades'],
            ['key' => 'zones',  'label' => 'Zonas'],
            ['key' => 'pos',    'label' => 'Puntos de venta'],
        ] as $t)
        <button type="button" @click="tab = '{{ $t['key'] }}'"
                :class="tab === '{{ $t['key'] }}' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 rounded-lg text-sm transition-all">
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ══ TAB: ÁRBOL GEOGRÁFICO ══════════════════════════════════════════════ --}}
    <div x-show="tab === 'tree'" x-cloak>
        @foreach($countries as $country)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
            {{-- País --}}
            <div class="px-5 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
                <span class="text-lg">🌎</span>
                <div>
                    <p class="font-bold text-blue-900">{{ $country->name }}</p>
                    <p class="text-xs text-blue-600">{{ $country->departments->count() }} departamentos</p>
                </div>
            </div>

            {{-- Departamentos --}}
            <div class="divide-y divide-gray-50">
                @foreach($country->departments as $dept)
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-mono bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">{{ $dept->code }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $dept->name }}</span>
                            <span class="text-xs text-gray-400">{{ $dept->cities_count }} ciudad(es)</span>
                        </div>
                        <svg class="w-4 h-4 flex-shrink-0 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-transition class="bg-gray-50 border-t border-gray-100">
                        @php $deptCities = $cities->where('department_id', $dept->id); @endphp
                        @forelse($deptCities as $city)
                        <div class="flex items-center justify-between px-8 py-2.5 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                <span class="text-sm text-gray-700">{{ $city->name }}</span>
                                @if($city->zones_count > 0)
                                    <span class="text-xs text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">{{ $city->zones_count }} zonas</span>
                                @endif
                                @if($city->points_of_sale_count > 0)
                                    <span class="text-xs text-green-600 bg-green-50 px-1.5 py-0.5 rounded">{{ $city->points_of_sale_count }} PDV</span>
                                @endif
                            </div>
                            <span class="text-xs {{ $city->is_active ? 'text-green-500' : 'text-gray-400' }}">
                                {{ $city->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        @empty
                        <p class="px-8 py-3 text-xs text-gray-400">Sin ciudades registradas.</p>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══ TAB: CIUDADES ══════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'cities'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Tabla --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Ciudades registradas ({{ $cities->count() }})</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="text-left px-5 py-3">Ciudad</th>
                            <th class="text-left px-4 py-3">Departamento</th>
                            <th class="text-center px-4 py-3">Zonas</th>
                            <th class="text-center px-4 py-3">PDV</th>
                            <th class="text-center px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($cities as $city)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $city->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $city->department->name }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($city->zones_count > 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold">{{ $city->zones_count }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($city->points_of_sale_count > 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-700 text-xs font-bold">{{ $city->points_of_sale_count }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $city->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $city->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $city->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Formulario nueva ciudad --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Nueva ciudad</h3>
                <form method="POST" action="{{ route('admin.geography.cities.store') }}" id="form-city">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Departamento <span class="text-red-500">*</span></label>
                            <select name="department_id" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('department_id') border-red-400 @enderror">
                                <option value="">— Selecciona —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="Ej: Bucaramanga"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Código <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" name="code" value="{{ old('code') }}"
                                   placeholder="Ej: BMG"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                            Crear ciudad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ TAB: ZONAS ═════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'zones'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Tabla --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Zonas registradas ({{ $zones->count() }})</h3>
                </div>
                @if($zones->isEmpty())
                    <div class="py-12 text-center text-gray-400 text-sm">No hay zonas registradas.</div>
                @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="text-left px-5 py-3">Zona</th>
                            <th class="text-left px-4 py-3">Ciudad</th>
                            <th class="text-left px-4 py-3">Descripción</th>
                            <th class="text-center px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($zones as $zone)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $zone->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $zone->city->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $zone->description ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $zone->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $zone->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $zone->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Formulario nueva zona --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Nueva zona</h3>
                <form method="POST" action="{{ route('admin.geography.zones.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ciudad <span class="text-red-500">*</span></label>
                            <select name="city_id" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Selecciona —</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }} ({{ $city->department->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('city_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="Ej: Zona Norte"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Descripción <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" name="description" value="{{ old('description') }}"
                                   placeholder="Ej: Cubre localidades norte"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                            Crear zona
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ TAB: PUNTOS DE VENTA ═══════════════════════════════════════════════ --}}
    <div x-show="tab === 'pos'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Tabla + filtros --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Filtros --}}
                <form method="GET" action="{{ route('admin.geography.index') }}" onsubmit="document.getElementById('tab-target').value='pos'">
                    <input type="hidden" name="tab" id="tab-target" value="pos">
                    <div class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Nombre o código..."
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ciudad</label>
                            <select name="city_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todas</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Activo</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Filtrar
                        </button>
                        @if(request()->hasAny(['search','city_id','status']))
                            <a href="{{ route('admin.geography.index') }}?tab=pos" class="text-sm text-gray-500 hover:text-gray-700 py-2">Limpiar</a>
                        @endif
                    </div>
                </form>

                {{-- Tabla PDV --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Puntos de venta</h3>
                        <p class="text-xs text-gray-400">{{ number_format($pointsOfSale->total()) }} en total</p>
                    </div>

                    @if($pointsOfSale->isEmpty())
                        <div class="py-12 text-center">
                            <p class="text-gray-400 text-sm">No hay puntos de venta registrados.</p>
                            <p class="text-xs text-gray-300 mt-1">Usa el formulario para crear el primero.</p>
                        </div>
                    @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                <th class="text-left px-5 py-3">Nombre</th>
                                <th class="text-left px-4 py-3">Código</th>
                                <th class="text-left px-4 py-3">Ciudad / Zona</th>
                                <th class="text-left px-4 py-3">Contacto</th>
                                <th class="text-center px-4 py-3">Estado</th>
                                <th class="text-right px-5 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pointsOfSale as $pos)
                            <tr class="hover:bg-gray-50/60 {{ $pos->status === 'inactive' ? 'opacity-60' : '' }}">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800">{{ $pos->name }}</p>
                                    @if($pos->address)
                                        <p class="text-xs text-gray-400 truncate max-w-[180px]" title="{{ $pos->address }}">{{ $pos->address }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $pos->code }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    <p>{{ $pos->city->name }}</p>
                                    @if($pos->zone)
                                        <p class="text-gray-400">{{ $pos->zone->name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    <p>{{ $pos->contact_name ?? '—' }}</p>
                                    @if($pos->phone)
                                        <p class="text-gray-400">{{ $pos->phone }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $pos->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $pos->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $pos->status === 'active' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Toggle estado --}}
                                        <form method="POST" action="{{ route('admin.geography.pos.toggle', $pos) }}">
                                            @csrf @method('PATCH')
                                            <button type="button"
                                                    onclick="if(confirm('¿{{ $pos->status === 'active' ? 'Desactivar' : 'Activar' }} este PDV?')) this.closest('form').submit()"
                                                    class="text-xs {{ $pos->status === 'active' ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }} font-medium">
                                                {{ $pos->status === 'active' ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                        {{-- Eliminar --}}
                                        <form method="POST" action="{{ route('admin.geography.pos.destroy', $pos) }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    onclick="if(confirm('¿Eliminar el PDV \"{{ addslashes($pos->name) }}\"?')) this.closest('form').submit()"
                                                    class="text-xs text-red-500 hover:text-red-700 font-medium">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($pointsOfSale->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Mostrando {{ $pointsOfSale->firstItem() }}–{{ $pointsOfSale->lastItem() }} de {{ number_format($pointsOfSale->total()) }}
                        </p>
                        {{ $pointsOfSale->links() }}
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- Formulario nuevo PDV --}}
            <div class="bg-white rounded-xl shadow-sm p-5" x-data="posForm()" x-init="init()">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Nuevo punto de venta</h3>
                <form method="POST" action="{{ route('admin.geography.pos.store') }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="Ej: Tienda Norte Centro"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Código <span class="text-red-500">*</span></label>
                            <input type="text" name="code" value="{{ old('code') }}" required
                                   placeholder="Ej: PDV-BOG-001"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-400 @enderror">
                            @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ciudad <span class="text-red-500">*</span></label>
                            <select name="city_id" required x-model="selectedCity" @change="loadZones()"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('city_id') border-red-400 @enderror">
                                <option value="">— Selecciona —</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }} ({{ $city->department->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('city_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Zona <span class="text-gray-400">(opcional)</span></label>
                            <select name="zone_id"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Sin zona —</option>
                                <template x-for="zone in availableZones" :key="zone.id">
                                    <option :value="zone.id" x-text="zone.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Dirección <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                   placeholder="Ej: Cra 7 # 12-34"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   placeholder="Ej: 6011234567"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Contacto <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                                   placeholder="Nombre del responsable"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors mt-2">
                            Crear punto de venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
// Zonas por ciudad para el formulario de PDV
const zonasPorCiudad = @json($zones->groupBy('city_id')->map(fn($zs) => $zs->map(fn($z) => ['id' => $z->id, 'name' => $z->name])->values()));

function posForm() {
    return {
        selectedCity: @json(old('city_id', '')),
        availableZones: [],
        init() { if (this.selectedCity) this.loadZones(); },
        loadZones() {
            this.availableZones = zonasPorCiudad[this.selectedCity] ?? [];
        }
    };
}
</script>

@endsection
