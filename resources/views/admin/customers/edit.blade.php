@extends('layouts.admin')
@section('title', 'Editar Cliente')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.customers.show', $customer) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        ← {{ $customer->full_name }}
    </a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Editar Cliente</h1>
</div>

<form method="POST" action="{{ route('admin.customers.update', $customer) }}"
      x-data="customerForm()" class="max-w-3xl">
    @csrf @method('PUT')

    {{-- Datos básicos --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Datos básicos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required maxlength="100"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                <input type="text" name="lastname" value="{{ old('lastname', $customer->lastname) }}" maxlength="100"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Celular <span class="text-red-500">*</span>
                </label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required maxlength="20"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-300 @enderror">
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" maxlength="150"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-300 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                <select name="document_type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Seleccionar —</option>
                    @foreach(['CC'=>'Cédula de Ciudadanía','CE'=>'Cédula de Extranjería','PA'=>'Pasaporte','TI'=>'Tarjeta de Identidad','RC'=>'Registro Civil','NIT'=>'NIT','DE'=>'Documento Extranjero'] as $val => $label)
                    <option value="{{ $val }}" {{ old('document_type', $customer->document_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número de documento</label>
                <input type="text" name="document_number" value="{{ old('document_number', $customer->document_number) }}" maxlength="30"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('document_number') border-red-300 @enderror">
                @error('document_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>
    </div>

    {{-- Datos adicionales --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Datos adicionales</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                <select x-model="deptId"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Seleccionar —</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad / Municipio</label>
                <select name="city_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Seleccionar —</option>
                    @foreach($cities as $city)
                    <option value="{{ $city->id }}"
                            data-dept="{{ $city->department_id }}"
                            x-show="!deptId || deptId == '{{ $city->department_id }}'"
                            {{ old('city_id', $customer->city_id) == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                    @endforeach
                </select>
                @error('city_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                <input type="date" name="birth_date"
                       value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                <select name="gender"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Seleccionar —</option>
                    <option value="M" {{ old('gender', $customer->gender) === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('gender', $customer->gender) === 'F' ? 'selected' : '' }}>Femenino</option>
                    <option value="O" {{ old('gender', $customer->gender) === 'O' ? 'selected' : '' }}>Otro</option>
                    <option value="N" {{ old('gender', $customer->gender) === 'N' ? 'selected' : '' }}>Prefiero no indicar</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input type="text" name="address" value="{{ old('address', $customer->address) }}" maxlength="255"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

        </div>
    </div>

    {{-- Estado --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Estado del cliente</h2>
        <div class="flex gap-3 flex-wrap">
            @foreach(['active' => ['label' => 'Activo', 'color' => 'green'], 'blocked' => ['label' => 'Bloqueado', 'color' => 'red'], 'unsubscribed' => ['label' => 'Desuscrito', 'color' => 'gray']] as $val => $cfg)
            <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg border-2 cursor-pointer transition-all
                   {{ old('status', $customer->status) === $val ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                <input type="radio" name="status" value="{{ $val }}"
                       {{ old('status', $customer->status) === $val ? 'checked' : '' }}
                       class="text-blue-600">
                <span class="text-sm font-medium">{{ $cfg['label'] }}</span>
            </label>
            @endforeach
        </div>
        @error('status') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Aceptación de datos personales --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Autorización de datos personales</h2>
        <p class="text-xs text-gray-500 mb-4">Ley 1581 de 2012</p>

        {{-- Estado actual --}}
        @if($customer->data_treatment_accepted)
        <div class="flex items-center gap-2 mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-green-800">Datos personales autorizados</p>
                @if($customer->data_treatment_accepted_at)
                <p class="text-xs text-green-600">{{ $customer->data_treatment_accepted_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>
        @else
        {{-- Permitir registrar aceptación --}}
        @if($privacyDoc)
        <div class="mb-3 p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 max-h-28 overflow-y-auto leading-relaxed">
            {!! nl2br(e(strip_tags($privacyDoc->content))) !!}
        </div>
        @endif
        <label class="flex items-start gap-3 cursor-pointer group mb-3">
            <input type="checkbox" name="accept_privacy" value="1"
                   {{ old('accept_privacy') ? 'checked' : '' }}
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700 group-hover:text-gray-900">
                El cliente autoriza el tratamiento de sus datos personales
                @if($privacyDoc)
                según la <a href="{{ route('admin.legal-documents.show', $privacyDoc) }}" target="_blank"
                   class="text-blue-600 hover:underline">Política de Privacidad v{{ $privacyDoc->version }}</a>.
                @endif
            </span>
        </label>
        @endif

        {{-- Consentimiento SMS --}}
        @if($smsConsentDoc && !in_array('sms_consent', $acceptedTypes))
        <label class="flex items-start gap-3 cursor-pointer group">
            <input type="checkbox" name="accept_sms" value="1"
                   {{ old('accept_sms') ? 'checked' : '' }}
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700 group-hover:text-gray-900">
                El cliente autoriza el envío de mensajes SMS según el
                <a href="{{ route('admin.legal-documents.show', $smsConsentDoc) }}" target="_blank"
                   class="text-blue-600 hover:underline">Consentimiento SMS v{{ $smsConsentDoc->version }}</a>.
            </span>
        </label>
        @elseif(in_array('sms_consent', $acceptedTypes))
        <div class="flex items-center gap-2 mt-2 text-xs text-green-700">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Consentimiento SMS ya registrado.
        </div>
        @endif

        {{-- Historial de aceptaciones --}}
        @if($customer->acceptances->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-medium text-gray-500 mb-2">Historial de aceptaciones</p>
            <div class="space-y-1">
                @foreach($customer->acceptances as $acc)
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $acc->legalDocument?->title ?? 'Documento eliminado' }}</span>
                    <span>{{ $acc->accepted_at->format('d/m/Y H:i') }} · {{ strtoupper($acc->channel) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Acciones --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
            Guardar Cambios
        </button>
        <a href="{{ route('admin.customers.show', $customer) }}"
           class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
            Cancelar
        </a>
    </div>
</form>

<script>
function customerForm() {
    return {
        deptId: '{{ $customer->city?->department_id ?? '' }}',
    };
}
</script>
@endsection
