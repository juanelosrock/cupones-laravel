@extends('layouts.admin')
@section('title', $customer->full_name)
@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('admin.customers.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Clientes</a>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $customer->full_name }}</h1>
        <p class="text-gray-500 text-sm mt-0.5">
            @if($customer->document_type) {{ $customer->document_type }} {{ $customer->document_number }} · @endif
            {{ $customer->phone }}
            @if($customer->email) · {{ $customer->email }} @endif
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.customers.edit', $customer) }}"
           class="flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
        @if($customer->status === 'active')
        <form method="POST" action="{{ route('admin.customers.block', $customer) }}"
              onsubmit="return confirm('¿Bloquear este cliente?')">
            @csrf
            <button class="flex items-center gap-1.5 bg-white hover:bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Bloquear
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.customers.unblock', $customer) }}">
            @csrf
            <button class="flex items-center gap-1.5 bg-white hover:bg-green-50 text-green-600 border border-green-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Desbloquear
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Columna izquierda --}}
    <div class="space-y-5">

        {{-- Info del cliente --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Información</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Estado</dt>
                    <dd>
                        @php $sc = ['active' => ['bg-green-100 text-green-700', 'Activo'], 'blocked' => ['bg-red-100 text-red-700', 'Bloqueado'], 'unsubscribed' => ['bg-gray-100 text-gray-600', 'Desuscrito']] @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full {{ ($sc[$customer->status] ?? ['bg-gray-100 text-gray-600', ''])[0] }}">
                            {{ ($sc[$customer->status] ?? ['', ucfirst($customer->status)])[1] }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Ciudad</dt>
                    <dd class="text-gray-800">{{ $customer->city?->name ?? '—' }}</dd>
                </div>
                @if($customer->city?->department)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Departamento</dt>
                    <dd class="text-gray-800">{{ $customer->city->department->name }}</dd>
                </div>
                @endif
                @if($customer->birth_date)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Nacimiento</dt>
                    <dd class="text-gray-800">{{ $customer->birth_date->format('d/m/Y') }}</dd>
                </div>
                @endif
                @if($customer->gender)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Género</dt>
                    <dd class="text-gray-800">{{ ['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro', 'N' => 'No indica'][$customer->gender] ?? $customer->gender }}</dd>
                </div>
                @endif
                @if($customer->address)
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500 flex-shrink-0">Dirección</dt>
                    <dd class="text-gray-800 text-right">{{ $customer->address }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Canal registro</dt>
                    <dd class="text-xs uppercase text-gray-600 font-medium">{{ $customer->created_via }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Registrado</dt>
                    <dd class="text-gray-800">{{ $customer->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Datos personales (Ley 1581) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Datos Personales — Ley 1581</h3>

            @if($customer->data_treatment_accepted)
            <div class="flex items-start gap-2 p-3 bg-green-50 border border-green-200 rounded-lg mb-3">
                <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-green-800">Tratamiento autorizado</p>
                    @if($customer->data_treatment_accepted_at)
                    <p class="text-xs text-green-600">{{ $customer->data_treatment_accepted_at->format('d/m/Y H:i') }}</p>
                    @endif
                    @if($customer->acceptance_ip)
                    <p class="text-xs text-green-600/70">IP: {{ $customer->acceptance_ip }}</p>
                    @endif
                </div>
            </div>
            @else
            <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg mb-3">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800">Pendiente de autorización</p>
                    <a href="{{ route('admin.customers.edit', $customer) }}" class="text-xs text-amber-700 underline">Registrar ahora</a>
                </div>
            </div>
            @endif

            {{-- Historial de aceptaciones --}}
            @if($customer->acceptances->isNotEmpty())
            <p class="text-xs font-semibold text-gray-500 mb-2">Historial de aceptaciones</p>
            <div class="space-y-2">
                @foreach($customer->acceptances->sortByDesc('accepted_at') as $acc)
                <div class="text-xs border border-gray-100 rounded-lg p-2.5">
                    <p class="font-medium text-gray-700">{{ $acc->legalDocument?->title ?? '—' }}</p>
                    <div class="flex items-center gap-2 mt-0.5 text-gray-400">
                        @if($acc->legalDocument)
                        <span>v{{ $acc->legalDocument->version }}</span>
                        <span>·</span>
                        @endif
                        <span class="uppercase">{{ $acc->channel }}</span>
                        <span>·</span>
                        <span>{{ $acc->accepted_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($acc->ip_address)
                    <p class="text-gray-300 mt-0.5">IP: {{ $acc->ip_address }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-400">Sin registros de aceptación.</p>
            @endif
        </div>

    </div>

    {{-- Columna derecha — Redenciones --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Historial de Redenciones
                    <span class="ml-1 text-sm font-normal text-gray-400">({{ $redemptions->total() }})</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase">
                            <th class="px-4 py-2 text-left">Código</th>
                            <th class="px-4 py-2 text-left">Lote</th>
                            <th class="px-4 py-2 text-left">Descuento</th>
                            <th class="px-4 py-2 text-left">Canal</th>
                            <th class="px-4 py-2 text-left">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    @forelse($redemptions as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono">{{ $r->coupon->code }}</code>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-600">{{ $r->coupon->batch?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-green-600 font-medium text-xs">
                            -${{ number_format($r->discount_applied, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 text-xs uppercase text-gray-500">{{ $r->channel }}</td>
                        <td class="px-4 py-2 text-xs text-gray-400 whitespace-nowrap">{{ $r->redeemed_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
                            Este cliente no ha redimido cupones aún.
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t">{{ $redemptions->links() }}</div>
        </div>
    </div>

</div>
@endsection
