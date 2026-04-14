@extends('layouts.admin')
@section('title', 'Lotes de Cupones')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Lotes de Cupones</h1>
    <a href="{{ route('admin.coupon-batches.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Nuevo Lote</a>
</div>

<div class="bg-white rounded-xl shadow-sm">
    <div class="p-4 border-b flex gap-3">
        <form method="GET" class="flex gap-3 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre..."
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Todos los estados</option>
                @foreach(['draft','active','paused','expired','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Filtrar</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 bg-gray-50 border-b">
                <th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Tipo</th>
                <th class="px-4 py-3">Descuento</th><th class="px-4 py-3">Vigencia</th>
                <th class="px-4 py-3">Códigos</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Acciones</th>
            </tr></thead>
            <tbody class="divide-y">
            @forelse($batches as $b)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">{{ $b->name }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded-full {{ $b->code_type === 'unique' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $b->code_type === 'unique' ? '🔒 Único' : '🌐 General' }}
                    </span>
                </td>
                <td class="px-4 py-3 font-semibold text-green-700">
                    {{ $b->discount_type === 'percentage' ? $b->discount_value.'%' : '$'.number_format($b->discount_value,0,',','.') }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $b->start_date->format('d/m/Y') }} → {{ $b->end_date->format('d/m/Y') }}</td>
                <td class="px-4 py-3">{{ number_format($b->coupons_count) }}</td>
                <td class="px-4 py-3">
                    @php $colors = ['draft'=>'gray','active'=>'green','paused'=>'yellow','expired'=>'red','cancelled'=>'red'] @endphp
                    <span class="text-xs px-2 py-1 rounded-full bg-{{ $colors[$b->status] ?? 'gray' }}-100 text-{{ $colors[$b->status] ?? 'gray' }}-700">
                        {{ ucfirst($b->status) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('admin.coupon-batches.show', $b) }}" class="text-blue-600 hover:underline text-xs mr-2">Ver</a>
                    @if($b->status === 'draft')
                    <form method="POST" action="{{ route('admin.coupon-batches.activate', $b) }}" class="inline">
                        @csrf <button class="text-green-600 hover:underline text-xs">Activar</button>
                    </form>
                    @elseif($b->status === 'active')
                    <form method="POST" action="{{ route('admin.coupon-batches.pause', $b) }}" class="inline">
                        @csrf <button class="text-yellow-600 hover:underline text-xs">Pausar</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No hay lotes de cupones</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $batches->links() }}</div>
</div>
@endsection