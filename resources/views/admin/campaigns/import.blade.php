@extends('layouts.admin')
@section('title', 'Importar Clientes — ' . $campaign->name)
@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.campaigns.index') }}" class="hover:text-gray-600">Campañas</a>
    <span>/</span>
    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="hover:text-gray-600 truncate max-w-xs">{{ $campaign->name }}</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Importar clientes</span>
</div>

<div class="max-w-2xl">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Importar base de clientes</h1>
        <p class="text-sm text-gray-500 mt-1">
            Sube un archivo CSV o Excel con tus clientes para asociarlos a la campaña
            <strong class="text-gray-700">{{ $campaign->name }}</strong>.
        </p>
    </div>

    {{-- Errores de importación previos --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="mb-5 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-yellow-800 mb-2">⚠ Algunas filas tuvieron problemas:</p>
            <ul class="text-xs text-yellow-700 space-y-1 max-h-32 overflow-y-auto">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Aviso campaña de autorización --}}
    @if($campaign->type === 'autorizacion')
    <div class="mb-5 bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-start gap-3">
        <span class="text-2xl flex-shrink-0">📋</span>
        <div>
            <p class="text-sm font-semibold text-amber-800">Campaña de Autorización de Datos</p>
            <p class="text-xs text-amber-700 mt-1">
                Los clientes que ya tienen <strong>autorización de datos registrada</strong> serán
                <strong>excluidos automáticamente</strong> al importar. Solo se vincularán a esta campaña
                los clientes que aún no han autorizado el tratamiento de sus datos personales.
            </p>
        </div>
    </div>
    @endif

    {{-- Upload form --}}
    <form method="POST" action="{{ route('admin.campaigns.import', $campaign) }}"
          enctype="multipart/form-data" x-data="importForm()">
        @csrf

        {{-- Drop zone --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Archivo</h2>

            <div class="border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
                 :class="file ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                 @click="$refs.fileInput.click()"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="handleDrop($event)">

                <input type="file" name="file" x-ref="fileInput" class="sr-only"
                       accept=".csv,.txt,.xlsx,.xls"
                       @change="handleFile($event)">

                <template x-if="!file">
                    <div>
                        <div class="text-4xl mb-3">📂</div>
                        <p class="text-sm font-medium text-gray-700">Haz clic o arrastra tu archivo aquí</p>
                        <p class="text-xs text-gray-400 mt-1">CSV, TXT, XLS o XLSX — máximo 5 MB</p>
                    </div>
                </template>
                <template x-if="file">
                    <div>
                        <div class="text-4xl mb-3">✅</div>
                        <p class="text-sm font-semibold text-gray-800" x-text="file.name"></p>
                        <p class="text-xs text-gray-400 mt-1" x-text="formatSize(file.size)"></p>
                        <button type="button" @click.stop="clearFile()"
                                class="mt-2 text-xs text-red-500 hover:text-red-700">Cambiar archivo</button>
                    </div>
                </template>
            </div>

            @error('file')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Formato esperado --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Columnas del archivo</h2>
            <p class="text-xs text-gray-500 mb-3">
                El archivo debe tener una <strong>fila de cabecera</strong>. Los nombres de columna se reconocen automáticamente
                (mayúsculas/minúsculas/tildes no importan).
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 font-semibold uppercase tracking-wide">
                            <th class="text-left px-3 py-2">Columna (acepta estos nombres)</th>
                            <th class="text-left px-3 py-2">Requerida</th>
                            <th class="text-left px-3 py-2">Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <code class="bg-gray-100 px-1 rounded">telefono</code>
                                <code class="bg-gray-100 px-1 rounded">celular</code>
                                <code class="bg-gray-100 px-1 rounded">movil</code>
                            </td>
                            <td class="px-3 py-2"><span class="text-red-500 font-semibold">Sí</span></td>
                            <td class="px-3 py-2 text-gray-500">3001234567</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <code class="bg-gray-100 px-1 rounded">nombre</code>
                                <code class="bg-gray-100 px-1 rounded">name</code>
                            </td>
                            <td class="px-3 py-2 text-gray-400">No</td>
                            <td class="px-3 py-2 text-gray-500">María</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <code class="bg-gray-100 px-1 rounded">apellido</code>
                                <code class="bg-gray-100 px-1 rounded">apellidos</code>
                            </td>
                            <td class="px-3 py-2 text-gray-400">No</td>
                            <td class="px-3 py-2 text-gray-500">García</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <code class="bg-gray-100 px-1 rounded">documento</code>
                                <code class="bg-gray-100 px-1 rounded">cedula</code>
                            </td>
                            <td class="px-3 py-2 text-gray-400">No</td>
                            <td class="px-3 py-2 text-gray-500">1020304050</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <code class="bg-gray-100 px-1 rounded">email</code>
                                <code class="bg-gray-100 px-1 rounded">correo</code>
                            </td>
                            <td class="px-3 py-2 text-gray-400">No</td>
                            <td class="px-3 py-2 text-gray-500">maria@email.com</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <code class="bg-gray-100 px-1 rounded">ciudad</code>
                                <code class="bg-gray-100 px-1 rounded">city</code>
                            </td>
                            <td class="px-3 py-2 text-gray-400">No</td>
                            <td class="px-3 py-2 text-gray-500">Bogotá</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Ejemplo descargable --}}
            <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-blue-800">¿No tienes el formato correcto?</p>
                    <p class="text-xs text-blue-600 mt-0.5">Descarga la plantilla de ejemplo y complétala</p>
                </div>
                <a href="{{ route('admin.campaigns.import.template') }}"
                   class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg font-medium transition-colors">
                    Descargar plantilla
                </a>
            </div>
        </div>

        {{-- Comportamiento --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">¿Qué hace la importación?</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <span class="text-green-500 mt-0.5">✓</span>
                    <span>Si el teléfono o documento <strong>ya existe</strong> en la base de clientes, actualiza los datos faltantes sin sobreescribir.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">✓</span>
                    <span>Si el cliente es <strong>nuevo</strong>, lo crea en el sistema.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-purple-500 mt-0.5">✓</span>
                    <span>Todos los clientes quedan <strong>vinculados a esta campaña</strong> para filtrarlos en campañas SMS.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-gray-400 mt-0.5">✓</span>
                    <span>Si un cliente <strong>ya está vinculado</strong> a esta campaña, se omite sin crear duplicados.</span>
                </li>
            </ul>
        </div>

        {{-- Botones --}}
        <div class="flex gap-3">
            <button type="submit" :disabled="!file"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                <span x-show="!uploading">Importar clientes</span>
                <span x-show="uploading">Procesando...</span>
            </button>
            <a href="{{ route('admin.campaigns.show', $campaign) }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>

</div>

<script>
function importForm() {
    return {
        file: null,
        dragging: false,
        uploading: false,
        handleFile(e) {
            const f = e.target.files[0];
            if (f) this.file = f;
        },
        handleDrop(e) {
            this.dragging = false;
            const f = e.dataTransfer.files[0];
            if (f) {
                this.file = f;
                // Asignar al input real
                const dt = new DataTransfer();
                dt.items.add(f);
                this.$refs.fileInput.files = dt.files;
            }
        },
        clearFile() {
            this.file = null;
            this.$refs.fileInput.value = '';
        },
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
    }
}
</script>
@endsection
