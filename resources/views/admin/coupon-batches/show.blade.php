@extends('layouts.admin')
@section('title', $couponBatch->name)
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $couponBatch->name }}</h1>
        <p class="text-gray-500 text-sm">{{ $couponBatch->campaign?->name ?? 'Sin campaña' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.coupon-batches.edit', $couponBatch) }}"
           class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ✏ Editar
        </a>
        @if($couponBatch->status === 'draft' || $couponBatch->status === 'paused')
        <form method="POST" action="{{ route('admin.coupon-batches.activate', $couponBatch) }}">
            @csrf <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Activar</button>
        </form>
        @elseif($couponBatch->status === 'active')
        <form method="POST" action="{{ route('admin.coupon-batches.pause', $couponBatch) }}">
            @csrf <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Pausar</button>
        </form>
        @endif
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php $colors = ['draft'=>'gray','active'=>'green','paused'=>'yellow','expired'=>'red','cancelled'=>'red'] @endphp
    <div class="bg-white rounded-xl p-4 shadow-sm text-center">
        <div class="text-xs text-gray-500">Estado</div>
        <div class="mt-1 text-sm font-bold px-2 py-1 rounded-full inline-block bg-{{ $colors[$couponBatch->status]??'gray' }}-100 text-{{ $colors[$couponBatch->status]??'gray' }}-700">
            {{ ucfirst($couponBatch->status) }}
        </div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm text-center">
        <div class="text-xs text-gray-500">Redenciones Totales</div>
        <div class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($redemptionStats['total']) }}</div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm text-center">
        <div class="text-xs text-gray-500">Hoy</div>
        <div class="text-2xl font-bold text-green-700 mt-1">{{ $redemptionStats['today'] }}</div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm text-center">
        <div class="text-xs text-gray-500">Descuento Otorgado</div>
        <div class="text-xl font-bold text-purple-700 mt-1">${{ number_format($redemptionStats['total_discount'],0,',','.') }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Detalles</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Tipo código</dt><dd class="font-medium">{{ ucfirst($couponBatch->code_type) }}</dd></div>
                @if($couponBatch->general_code)
                <div class="flex justify-between"><dt class="text-gray-500">Código general</dt><dd><code class="bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $couponBatch->general_code }}</code></dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Descuento</dt>
                    <dd class="font-bold text-green-700">{{ $couponBatch->discount_type === 'percentage' ? $couponBatch->discount_value.'%' : '$'.number_format($couponBatch->discount_value,0,',','.') }}</dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Compra mínima</dt><dd>${{ number_format($couponBatch->min_purchase_amount,0,',','.') }}</dd></div>
                @if($couponBatch->max_purchase_amount)
                <div class="flex justify-between"><dt class="text-gray-500">Compra máxima</dt><dd>${{ number_format($couponBatch->max_purchase_amount,0,',','.') }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Inicio</dt><dd>{{ $couponBatch->start_date->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Vence</dt><dd>{{ $couponBatch->end_date->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Creado por</dt><dd>{{ $couponBatch->createdBy?->name ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Códigos ({{ number_format($coupons->total()) }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50 border-b text-left text-gray-500">
                        <th class="px-4 py-2">Código</th><th class="px-4 py-2">Estado</th><th class="px-4 py-2">Usos</th>
                    </tr></thead>
                    <tbody class="divide-y">
                    @foreach($coupons as $c)
                    <tr>
                        <td class="px-4 py-2"><code class="text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $c->code }}</code></td>
                        <td class="px-4 py-2">
                            @php $sc = ['active'=>'green','used'=>'red','expired'=>'gray','cancelled'=>'red'] @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc[$c->status]??'gray' }}-100 text-{{ $sc[$c->status]??'gray' }}-700">{{ ucfirst($c->status) }}</span>
                        </td>
                        <td class="px-4 py-2">{{ $c->times_used }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $coupons->links() }}</div>
        </div>
    </div>
</div>
@endsection