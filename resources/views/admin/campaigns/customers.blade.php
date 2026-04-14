@extends('layouts.admin')
@section('title', 'Clientes — ' . $campaign->name)
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.campaigns.index') }}" class="hover:text-gray-600">Campañas</a>
    <span>/</span>
    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="hover:text-gray-600 truncate max-w-xs">{{ $campaign->name }}</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Clientes</span>
</div>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Clientes de la campaña</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ number_format($customers->total()) }} clientes vinculados a <strong>{{ $campaign->name }}</strong></p>
    </div>
    <a href="{{ route('admin.campaigns.assign', $campaign) }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        + Asignar clientes
    </a>
</div>

@if($campaign->type === 'autorizacion')
<div class="mb-5 bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-start gap-3">
    <span class="text-xl flex-shrink-0">📋</span>
    <div>
        <p class="text-sm font-semibold text-amber-800">Campaña de Autorización de Datos</p>
        <p class="text-xs text-amber-700 mt-1">
            Esta campaña solo incluye clientes <strong>sin autorización de datos registrada</strong>.
            Al importar, los clientes que ya la tienen son excluidos automáticamente.
            Los clientes listados aquí son el público objetivo para obtener su consentimiento.
        </p>
    </div>
</div>
@endif

{{-- Search --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <form method="GET" class="flex gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nombre, teléfono o documento..."
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Buscar</button>
        @if(request('search'))
            <a href="{{ route('admin.campaigns.customers', $campaign) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Limpiar</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($customers->isEmpty())
        <div class="text-center py-16">
            <div class="text-5xl mb-4">👥</div>
            <p class="text-gray-500 font-medium">No hay clientes vinculados a esta campaña</p>
            <a href="{{ route('admin.campaigns.assign', $campaign) }}"
               class="mt-3 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                + Asignar primera base de clientes
            </a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Cliente</th>
                    <th class="text-left px-4 py-3">Teléfono</th>
                    <th class="text-left px-4 py-3">Documento</th>
                    <th class="text-left px-4 py-3">Ciudad</th>
                    <th class="text-left px-4 py-3">Origen</th>
                    <th class="text-left px-4 py-3">Vinculado</th>
                    <th class="text-right px-5 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($customers as $customer)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}{{ strtoupper(substr($customer->lastname ?? '', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $customer->name }} {{ $customer->lastname }}</p>
                                @if($customer->email)
                                    <p class="text-xs text-gray-400">{{ $customer->email }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        {{ $customer->document_type }} {{ $customer->document_number ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $customer->city?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @php $src = $customer->pivot->source ?? 'import'; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $src === 'import' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $src === 'import' ? 'Importado' : 'Manual' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($customer->pivot->created_at)->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">Ver</a>
                            <span class="text-gray-200">|</span>
                            <form method="POST"
                                  action="{{ route('admin.campaigns.customers.remove', [$campaign, $customer]) }}"
                                  onsubmit="return confirm('¿Desvincular a {{ addslashes($customer->name) }} de esta campaña?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-medium">Quitar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Mostrando {{ $customers->firstItem() }}–{{ $customers->lastItem() }} de {{ number_format($customers->total()) }} clientes
            </p>
            {{ $customers->links() }}
        </div>
    @endif
</div>

@endsection
