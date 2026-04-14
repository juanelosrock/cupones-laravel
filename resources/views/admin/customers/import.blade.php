@extends('layouts.admin')
@section('title', 'Importar Clientes')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.customers.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← Clientes</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Importar Clientes</h1>
</div>

<div class="max-w-2xl space-y-5">

    {{-- Errores de validación --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-semibold text-red-800 mb-1">Error en el archivo</p>
        @foreach($errors->all() as $e)
        <p class="text-xs text-red-700">{{ $e }}</p>
        @endforeach
    </div>
    @endif

    {{-- Paso 1: Plantilla --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center">1</span>
            <h2 class="text-base font-semibold text-gray-800">Descarga la plantilla</h2>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Usa la plantilla oficial para asegurar que el archivo tenga el formato correcto.
            Es compatible con Excel y Google Sheets.
        </p>

        <a href="{{ route('admin.customers.template') }}"
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Descargar plantilla_clientes.csv
        </a>

        {{-- Referencia de columnas --}}
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-xs border border-gray-200 rounded-lg">
                <thead>
                    <tr class="bg-gray-50 text-gray-500">
                        <th class="px-3 py-2 text-left font-semibold border-b">Columna</th>
                        <th class="px-3 py-2 text-left font-semibold border-b">Req.</th>
                        <th class="px-3 py-2 text-left font-semibold border-b">También acepta</th>
                        <th class="px-3 py-2 text-left font-semibold border-b">Descripción / Valores</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-3 py-2 font-mono">celular</td>
                        <td class="px-3 py-2"><span class="text-red-600 font-semibold">Sí</span></td>
                        <td class="px-3 py-2 font-mono text-gray-400">telefono, movil</td>
                        <td class="px-3 py-2 text-gray-500">10 dígitos. Ej: 3001234567</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">nombre</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">nombres</td>
                        <td class="px-3 py-2 text-gray-500">Nombre del cliente</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">apellido</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">apellidos</td>
                        <td class="px-3 py-2 text-gray-500">Apellido</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">correo</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">email, mail</td>
                        <td class="px-3 py-2 text-gray-500">Correo electrónico</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">tipo_documento</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">tipo_doc</td>
                        <td class="px-3 py-2 text-gray-500">CC, CE, PA, TI, NIT</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2 font-mono">numero_documento</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">documento, cedula</td>
                        <td class="px-3 py-2 text-gray-500">Número sin puntos ni espacios</td>
                    </tr>
                    <tr class="bg-green-50">
                        <td class="px-3 py-2 font-mono text-green-700">departamento</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">depto, dept</td>
                        <td class="px-3 py-2 text-gray-500">Nombre del departamento. Ej: Antioquia</td>
                    </tr>
                    <tr class="bg-green-50">
                        <td class="px-3 py-2 font-mono text-green-700">ciudad</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">codigo_ciudad <span class="text-gray-400">(DANE)</span></td>
                        <td class="px-3 py-2 text-gray-500">Nombre o código DANE. Ej: Bogotá / 11001</td>
                    </tr>
                    <tr class="bg-blue-50">
                        <td class="px-3 py-2 font-mono text-blue-700">acepta_datos</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">autoriza_datos</td>
                        <td class="px-3 py-2 text-gray-500"><strong>si</strong> / no — Ley 1581: autorización de datos</td>
                    </tr>
                    <tr class="bg-blue-50">
                        <td class="px-3 py-2 font-mono text-blue-700">acepta_sms</td>
                        <td class="px-3 py-2 text-gray-400">No</td>
                        <td class="px-3 py-2 font-mono text-gray-400">sms</td>
                        <td class="px-3 py-2 text-gray-500"><strong>si</strong> / no — Consentimiento envío de SMS</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-xs text-gray-400">
                Las columnas <span class="text-green-700 font-medium">departamento</span> y <span class="text-green-700 font-medium">ciudad</span>
                son las mismas que usa el filtro de asignación a campañas — puedes usar el mismo archivo para ambos módulos.
                El separador puede ser <code class="bg-gray-100 px-1 rounded">;</code> o <code class="bg-gray-100 px-1 rounded">,</code>.
            </p>
        </div>
    </div>

    {{-- Paso 2: Subir archivo --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center">2</span>
            <h2 class="text-base font-semibold text-gray-800">Sube el archivo CSV</h2>
        </div>

        <form method="POST" action="{{ route('admin.customers.import.process') }}"
              enctype="multipart/form-data" x-data="importForm()">
            @csrf

            {{-- Zona de drop --}}
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-colors cursor-pointer"
                 :class="dragging ? 'border-blue-400 bg-blue-50' : 'hover:border-gray-400'"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="handleDrop($event)"
                 @click="$refs.fileInput.click()">

                <input type="file" name="csv_file" accept=".csv,.txt"
                       x-ref="fileInput" class="hidden"
                       @change="handleFile($event.target.files[0])">

                <template x-if="!fileName">
                    <div>
                        <svg class="w-6 h-6 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm text-gray-500">Arrastra tu archivo aquí o <span class="text-blue-600 font-medium">haz clic para seleccionar</span></p>
                        <p class="text-xs text-gray-400 mt-1">CSV · Máximo 10 MB · Separador: punto y coma (;)</p>
                    </div>
                </template>

                <template x-if="fileName">
                    <div>
                        <svg class="w-6 h-6 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-800" x-text="fileName"></p>
                        <p class="text-xs text-gray-400 mt-1" x-text="fileSize"></p>
                        <button type="button" @click.stop="clearFile()"
                                class="mt-2 text-xs text-red-500 hover:text-red-700">Quitar archivo</button>
                    </div>
                </template>
            </div>

            {{-- Opciones de importación --}}
            <div class="mt-4 p-4 bg-gray-50 rounded-lg space-y-2">
                <p class="text-xs font-semibold text-gray-600 mb-2">Comportamiento ante duplicados</p>
                <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
                    <input type="radio" name="on_duplicate" value="skip" checked class="text-blue-600">
                    Omitir filas duplicadas (por teléfono o documento)
                </label>
                <p class="text-xs text-gray-400 pl-5">Los clientes ya existentes no se modifican.</p>
            </div>

            <button type="submit" :disabled="!fileName"
                    class="mt-5 w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Importar Clientes
            </button>
        </form>
    </div>

    {{-- Paso 3: Notas importantes --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800 mb-1">Importante — Ley 1581 (Datos Personales)</p>
                <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
                    <li>Solo importa clientes que ya han autorizado el tratamiento de sus datos.</li>
                    <li>La columna <code class="bg-amber-100 px-1 rounded font-mono">acepta_datos</code> debe ser <strong>si</strong> para clientes que otorgaron permiso.</li>
                    <li>Conserva el soporte físico o digital de la autorización.</li>
                    <li>Los registros de aceptación quedan guardados en el sistema con fecha y canal <em>import</em>.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<script>
function importForm() {
    return {
        dragging: false,
        fileName: null,
        fileSize: null,
        handleFile(file) {
            if (!file) return;
            this.fileName = file.name;
            this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
        },
        handleDrop(e) {
            this.dragging = false;
            const file = e.dataTransfer.files[0];
            if (file) {
                this.$refs.fileInput.files = e.dataTransfer.files;
                this.handleFile(file);
            }
        },
        clearFile() {
            this.fileName = null;
            this.fileSize = null;
            this.$refs.fileInput.value = '';
        },
    };
}
</script>
@endsection
