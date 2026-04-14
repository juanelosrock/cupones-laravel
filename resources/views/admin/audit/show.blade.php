@extends('layouts.admin')
@section('title', 'Detalle de auditoría')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.audit.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← Auditoría</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Registro #{{ $auditLog->id }}</h1>
</div>

<div class="max-w-3xl space-y-5">

    {{-- Cabecera --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-2 gap-x-8 gap-y-4">

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Fecha y hora</p>
                <p class="text-sm font-medium text-gray-900">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="text-xs text-gray-400">{{ $auditLog->created_at->diffForHumans() }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Acción</p>
                @php
                    $eventColors = [
                        'created'            => 'bg-green-100 text-green-700',
                        'updated'            => 'bg-blue-100 text-blue-700',
                        'deleted'            => 'bg-red-100 text-red-700',
                        'activated'          => 'bg-emerald-100 text-emerald-700',
                        'deactivated'        => 'bg-orange-100 text-orange-700',
                        'paused'             => 'bg-amber-100 text-amber-700',
                        'published'          => 'bg-teal-100 text-teal-700',
                        'revoked'            => 'bg-red-100 text-red-700',
                        'rotated_secret'     => 'bg-purple-100 text-purple-700',
                        'sent'               => 'bg-cyan-100 text-cyan-700',
                        'customers_assigned' => 'bg-indigo-100 text-indigo-700',
                        'customers_imported' => 'bg-violet-100 text-violet-700',
                        'duplicated'         => 'bg-gray-100 text-gray-600',
                        'cancelled'          => 'bg-rose-100 text-rose-700',
                        'retried'            => 'bg-yellow-100 text-yellow-700',
                    ];
                    $colorClass = $eventColors[$auditLog->event] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold {{ $colorClass }}">
                    {{ ucfirst(str_replace('_', ' ', $auditLog->event)) }}
                </span>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Usuario</p>
                @if($auditLog->user)
                <p class="text-sm font-medium text-gray-900">{{ $auditLog->user->name }}</p>
                <p class="text-xs text-gray-500">{{ $auditLog->user->email }}</p>
                @else
                <p class="text-sm text-gray-400 italic">Sistema / proceso automático</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Módulo / Entidad</p>
                @php
                    $entityShort = $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : null;
                    $entityLabels = [
                        'Campaign'         => 'Campaña',
                        'CouponBatch'      => 'Lote de Cupones',
                        'SmsCampaign'      => 'Campaña SMS',
                        'Customer'         => 'Cliente',
                        'User'             => 'Usuario',
                        'LegalDocument'    => 'Documento Legal',
                        'ApiClient'        => 'API Client',
                        'LandingPageConfig'=> 'Landing Page',
                    ];
                @endphp
                @if($entityShort)
                <p class="text-sm font-medium text-gray-900">
                    {{ $entityLabels[$entityShort] ?? $entityShort }}
                    @if($auditLog->auditable_id)
                        <span class="text-gray-400 font-normal">#{{ $auditLog->auditable_id }}</span>
                    @endif
                </p>
                @else
                <p class="text-sm text-gray-400">—</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Dirección IP</p>
                <p class="text-sm font-mono text-gray-700">{{ $auditLog->ip_address ?? '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">URL</p>
                <p class="text-xs text-gray-500 break-all font-mono">{{ $auditLog->url ?? '—' }}</p>
            </div>

            @if($auditLog->user_agent)
            <div class="col-span-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">User Agent</p>
                <p class="text-xs text-gray-500 break-all">{{ $auditLog->user_agent }}</p>
            </div>
            @endif

        </div>
    </div>

    {{-- Comparativa de valores --}}
    @if($auditLog->old_values || $auditLog->new_values)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">Cambios registrados</h2>

        @php
            $old = $auditLog->old_values ?? [];
            $new = $auditLog->new_values ?? [];
            $allKeys = collect(array_merge(array_keys($old), array_keys($new)))->unique()->sort()->values();
        @endphp

        @if($allKeys->isEmpty())
        <p class="text-sm text-gray-400 italic">Sin detalle de valores.</p>
        @else
        <div class="overflow-x-auto rounded-lg border border-gray-100">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider w-1/4">Campo</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-red-400 uppercase tracking-wider w-3/8">Valor anterior</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-green-500 uppercase tracking-wider w-3/8">Valor nuevo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($allKeys as $key)
                    @php
                        $oldVal = $old[$key] ?? null;
                        $newVal = $new[$key] ?? null;
                        $changed = $oldVal !== $newVal;
                        $fmt = fn($v) => is_array($v) ? json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (is_bool($v) ? ($v ? 'true' : 'false') : ($v === null ? '(vacío)' : $v));
                    @endphp
                    <tr class="{{ $changed ? 'bg-yellow-50/40' : '' }}">
                        <td class="px-4 py-2.5 font-mono text-gray-600 font-medium align-top">{{ $key }}</td>
                        <td class="px-4 py-2.5 text-red-700 align-top">
                            @if($oldVal !== null)
                            <span class="block whitespace-pre-wrap break-all">{{ $fmt($oldVal) }}</span>
                            @else
                            <span class="text-gray-300 italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-green-700 align-top">
                            @if($newVal !== null)
                            <span class="block whitespace-pre-wrap break-all">{{ $fmt($newVal) }}</span>
                            @else
                            <span class="text-gray-300 italic">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-2">Las filas resaltadas en amarillo indican campos que cambiaron.</p>
        @endif
    </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('admin.audit.index') }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
            ← Volver al log
        </a>
        @if(request()->header('referer'))
        <a href="{{ url()->previous() }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
            Volver atrás
        </a>
        @endif
    </div>

</div>

@endsection
