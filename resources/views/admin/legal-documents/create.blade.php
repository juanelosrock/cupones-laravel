@extends('layouts.admin')
@section('title', 'Nueva versión de documento legal')
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.legal-documents.index') }}" class="hover:text-gray-600">Documentos Legales</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Nueva versión</span>
</div>

<div class="max-w-3xl" x-data="legalCreate()">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nueva versión de documento legal</h1>
        <p class="text-sm text-gray-500 mt-1">Crea una nueva versión. Deberás publicarla manualmente para que sea la versión activa.</p>
    </div>

    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li class="flex items-center gap-1.5"><span class="text-red-400">•</span> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.legal-documents.store') }}">
        @csrf

        {{-- Tipo y versión --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">1. Tipo y versión</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                    <select name="type" required x-model="selectedType"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-400 @enderror">
                        <option value="">— Selecciona un tipo —</option>
                        @foreach($typeLabels as $key => $label)
                            <option value="{{ $key }}" {{ (old('type', request('type')) === $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Versión <span class="text-red-500">*</span>
                        <template x-if="latestForType">
                            <span class="text-xs text-gray-400 font-normal ml-1">(última: <span x-text="latestForType"></span>)</span>
                        </template>
                    </label>
                    <input type="text" name="version" value="{{ old('version') }}" required
                           placeholder="Ej: 1.0, 2.1, 2026-04"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('version') border-red-400 @enderror">
                    @error('version')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-400">Formato libre: 1.0, 2.1, 2026-Q2, etc.</p>
                </div>
            </div>
        </div>

        {{-- Título --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">2. Título del documento</h2>
            <input type="text" name="title" value="{{ old('title') }}" required
                   placeholder="Ej: Términos y Condiciones de Uso — CuponesHub"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror">
            @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-400">El título se muestra en la página pública y en las aceptaciones registradas.</p>
        </div>

        {{-- Contenido --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">3. Contenido</h2>
                <div class="flex items-center gap-2">
                    <button type="button" @click="preview = false"
                            :class="!preview ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                            class="text-xs px-3 py-1.5 rounded-lg transition-colors">
                        Editar
                    </button>
                    <button type="button" @click="preview = true"
                            :class="preview ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                            class="text-xs px-3 py-1.5 rounded-lg transition-colors">
                        Vista previa
                    </button>
                </div>
            </div>

            <p class="text-xs text-gray-500 mb-3">
                Acepta HTML básico: <code class="bg-gray-100 px-1 rounded">&lt;h2&gt;</code>
                <code class="bg-gray-100 px-1 rounded">&lt;p&gt;</code>
                <code class="bg-gray-100 px-1 rounded">&lt;ul&gt;&lt;li&gt;</code>
                <code class="bg-gray-100 px-1 rounded">&lt;strong&gt;</code>
                <code class="bg-gray-100 px-1 rounded">&lt;em&gt;</code>
            </p>

            <div x-show="!preview">
                <textarea name="content" id="content-editor" rows="18" required
                          x-model="content"
                          placeholder="Escribe el contenido del documento aquí. Puedes usar HTML básico."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y @error('content') border-red-400 @enderror">{{ old('content') }}</textarea>
                @error('content')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-400 text-right">
                    <span x-text="content.length.toLocaleString('es-CO')"></span> caracteres
                </p>
            </div>

            <div x-show="preview" x-cloak>
                <div class="min-h-[300px] border border-gray-200 rounded-lg px-5 py-4 prose prose-sm max-w-none text-gray-700"
                     x-html="content || '<p class=\'text-gray-400 italic\'>Sin contenido aún.</p>'">
                </div>
            </div>
        </div>

        {{-- Aviso --}}
        <div class="mb-5 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <div class="flex items-start gap-2.5">
                <span class="text-yellow-500 mt-0.5">⚠</span>
                <div>
                    <p class="text-sm font-semibold text-yellow-800">El documento se crea como borrador</p>
                    <p class="text-xs text-yellow-700 mt-0.5">
                        Después de crearlo, deberás hacer clic en <strong>Publicar</strong> para que sea la versión vigente y aparezca en las páginas públicas.
                        Las versiones anteriores pasarán a estado inactivo.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Crear documento
            </button>
            <a href="{{ route('admin.legal-documents.index') }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function legalCreate() {
    const latestVersions = @json($latestVersions);
    return {
        selectedType: @json(old('type', request('type', ''))),
        content: @json(old('content', '')),
        preview: false,
        get latestForType() {
            return this.selectedType ? (latestVersions[this.selectedType] ?? null) : null;
        }
    };
}
</script>

@endsection
