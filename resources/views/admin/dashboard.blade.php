@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-500 text-sm">Bienvenido, {{ auth()->user()->name }}</p>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @php
    $cards = [
        ['label'=>'Lotes Activos', 'value'=>$stats['active_batches'], 'icon'=>'🎫', 'color'=>'blue'],
        ['label'=>'Redenciones Hoy', 'value'=>$stats['redeemed_today'], 'icon'=>'✅', 'color'=>'green'],
        ['label'=>'Redenciones Mes', 'value'=>$stats['redeemed_month'], 'icon'=>'📊', 'color'=>'purple'],
        ['label'=>'Total Clientes', 'value'=>$stats['total_customers'], 'icon'=>'👥', 'color'=>'orange'],
        ['label'=>'Nuevos Esta Semana', 'value'=>$stats['new_customers_week'], 'icon'=>'🆕', 'color'=>'teal'],
        ['label'=>'Campañas Activas', 'value'=>$stats['active_campaigns'], 'icon'=>'📣', 'color'=>'blue'],
        ['label'=>'Total Cupones', 'value'=>number_format($stats['total_coupons']), 'icon'=>'🎟', 'color'=>'gray'],
        ['label'=>'Descuento Mes', 'value'=>'$'.number_format($stats['discount_given_month'],0,',','.'), 'icon'=>'💰', 'color'=>'red'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="text-3xl">{{ $c['icon'] }}</div>
        <div>
            <div class="text-2xl font-bold text-gray-900">{{ $c['value'] }}</div>
            <div class="text-xs text-gray-500">{{ $c['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Redemptions -->
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Últimas Redenciones</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Código</th><th class="pb-2">Cliente</th>
                    <th class="pb-2">Descuento</th><th class="pb-2">Fecha</th>
                </tr></thead>
                <tbody class="divide-y">
                @forelse($recentRedemptions as $r)
                <tr>
                    <td class="py-2"><code class="text-xs bg-gray-100 px-1 rounded">{{ $r->coupon->code }}</code></td>
                    <td class="py-2 text-gray-700">{{ $r->customer?->name ?? '—' }}</td>
                    <td class="py-2 text-green-600 font-medium">${{ number_format($r->discount_applied,0,',','.') }}</td>
                    <td class="py-2 text-gray-400 text-xs">{{ $r->redeemed_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-4 text-center text-gray-400">Sin redenciones aún</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <a href="{{ route('admin.redemptions.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Ver todas →</a>
    </div>

    <!-- Top Batches -->
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Lotes más Redimidos</h2>
        <div class="space-y-3">
        @forelse($topBatches as $b)
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-gray-800">{{ $b->name }}</div>
                <div class="text-xs text-gray-400">{{ ucfirst($b->discount_type) }}: {{ $b->discount_value }}{{ $b->discount_type === 'percentage' ? '%' : ' COP' }}</div>
            </div>
            <span class="text-sm font-bold text-blue-700">{{ $b->total_redeemed }} usos</span>
        </div>
        @empty
        <p class="text-gray-400 text-sm">Sin datos aún</p>
        @endforelse
        </div>
        <a href="{{ route('admin.coupon-batches.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Ver todos →</a>
    </div>
</div>
@endsection