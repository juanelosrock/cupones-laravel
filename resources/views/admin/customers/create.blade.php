@extends('layouts.admin')
@section('title', 'Nuevo Cliente')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.customers.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← Clientes</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Nuevo Cliente</h1>
</div>

<form method="POST" action="{{ route('admin.customers.store') }}"
      x-data="customerForm()" class="max-w-3xl">
    @csrf

    {{-- Datos básicos --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Datos básicos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                <input type="text" name="lastname" value="{{ old('lastname') }}" maxlength="100"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('lastname') border-red-300 @enderror">
                @error('lastname') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Celular <span class="text-red-500">*</span>
                </label>
                <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="20"
                       placeholder="Ej: 3001234567"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-300 @enderror">
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" maxlength="150"
                       placeholder="opcional@email.com"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-300 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento</label>
                <select name="document_type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Seleccionar —</option>
                    @foreach(['CC'=>'Cédula de Ciudadanía','CE'=>'Cédula de Extranjería','PA'=>'Pasaporte','TI'=>'Tarjeta de Identidad','RC'=>'Registro Civil','NIT'=>'NIT','DE'=>'Documento Extranjero'] as $val => $label)
                    <option value="{{ $val }}" {{ old('document_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('document_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número de documento</label>
                <input type="text" name="document_number" value="{{ old('document_number') }}" maxlength="30"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('document_number') border-red-300 @enderror">
                @error('document_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>
    </div>

    {{-- Datos adicionales --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Datos adicionales</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Departamento --}}
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

            {{-- Ciudad (filtrada por departamento) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad / Municipio</label>
                <select name="city_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('city_id') border-red-300 @enderror">
                    <option value="">— Seleccionar departamento primero —</option>
                    @foreach($cities as $city)
                    <option value="{{ $city->id }}"
                            data-dept="{{ $city->department_id }}"
                            x-show="!deptId || deptId == '{{ $city->department_id }}'"
                            {{ old('city_id') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                    @endforeach
                </select>
                @error('city_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('birth_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                <select name="gender"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Seleccionar —</option>
                    <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>Femenino</option>
                    <option value="O" {{ old('gender') === 'O' ? 'selected' : '' }}>Otro</option>
                    <option value="N" {{ old('gender') === 'N' ? 'selected' : '' }}>Prefiero no indicar</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input type="text" name="address" value="{{ old('address') }}" maxlength="255"
                       placeholder="Calle, carrera, barrio..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

        </div>
    </div>

    {{-- Aceptación de datos personales (Ley 1581) --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Autorización de datos personales</h2>
        <p class="text-xs text-gray-500 mb-4">Ley 1581 de 2012 — Protección de datos personales en Colombia</p>

        @if($privacyDoc)
        <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 max-h-32 overflow-y-auto leading-relaxed">
            {!! nl2br(e(strip_tags($privacyDoc->content))) !!}
        </div>
        @endif

        <label class="flex items-start gap-3 cursor-pointer group mb-3">
            <input type="checkbox" name="accept_privacy" value="1"
                   {{ old('accept_privacy') ? 'checked' : '' }}
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700 group-hover:text-gray-900">
                El cliente autoriza el tratamiento de sus datos personales según la
                @if($privacyDoc)
                <a href="{{ route('admin.legal-documents.show', $privacyDoc) }}" target="_blank"
                   class="text-blue-600 hover:underline">Política de Privacidad v{{ $privacyDoc->version }}</a>.
                @else
                política de privacidad vigente.
                @endif
                <span class="text-red-500">*</span>
            </span>
        </label>

        @if($smsConsentDoc)
        <label class="flex items-start gap-3 cursor-pointer group">
            <input type="checkbox" name="accept_sms" value="1"
                   {{ old('accept_sms') ? 'checked' : '' }}
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700 group-hover:text-gray-900">
                El cliente autoriza el envío de mensajes SMS y comunicaciones comerciales
                según el
                <a href="{{ route('admin.legal-documents.show', $smsConsentDoc) }}" target="_blank"
                   class="text-blue-600 hover:underline">Consentimiento SMS v{{ $smsConsentDoc->version }}</a>.
            </span>
        </label>
        @endif

        @if(!$privacyDoc && !$smsConsentDoc)
        <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg p-3">
            No hay documentos legales activos. Publica una
            <a href="{{ route('admin.legal-documents.create', ['type' => 'privacy']) }}" class="underline">Política de Privacidad</a>
            para activar este bloque.
        </p>
        @endif
    </div>

    {{-- Acciones --}}
    <div class="flex gap-3">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-colors">
            Crear Cliente
        </button>
        <a href="{{ route('admin.customers.index') }}"
           class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
            Cancelar
        </a>
    </div>
</form>

<script>
function customerForm() {
    return {
        deptId: '{{ old('city_id') ? \App\Models\City::find(old('city_id'))?->department_id : '' }}',
    };
}
</script>
@endsection
