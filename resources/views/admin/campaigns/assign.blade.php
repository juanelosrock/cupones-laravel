@extends('layouts.admin')
@section('title', 'Asignar Clientes — ' . $campaign->name)
@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.campaigns.index') }}" class="hover:text-gray-600">Campañas</a>
    <span>/</span>
    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="hover:text-gray-600 truncate max-w-xs">{{ $campaign->name }}</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Asignar clientes</span>
</div>

<div class="flex items-start justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Asignar clientes</h1>
        <p class="text-sm text-gray-500 mt-0.5">Importa nuevos clientes y/o segmenta los existentes para vincularlos a esta campaña.</p>
    </div>
    <div class="flex items-center gap-2">
        @if($campaign->type === 'autorizacion')
        <span class="text-xs px-3 py-1.5 bg-amber-100 text-amber-700 rounded-full font-medium">📋 Campaña de Autorización</span>
        @endif
        <a href="{{ route('admin.campaigns.customers', $campaign) }}"
           class="text-xs bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded-lg transition-colors">
            Ver clientes asignados →
        </a>
    </div>
</div>

{{-- Regla activa del tipo de campaña --}}
@if($campaign->type === 'autorizacion')
<div class="mb-5 bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-start gap-3">
    <span class="text-xl flex-shrink-0 mt-0.5">📋</span>
    <div>
        <p class="text-sm font-semibold text-amber-800">Regla activa: Campaña de Autorización de Datos</p>
        <p class="text-xs text-amber-700 mt-1">
            Solo se asignarán clientes que <strong>aún no han autorizado</strong> el tratamiento de sus datos personales.
            Los clientes con autorización ya registrada son excluidos automáticamente.
        </p>
    </div>
</div>
@else
<div class="mb-5 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
    <span class="text-xl flex-shrink-0 mt-0.5">🔒</span>
    <div>
        <p class="text-sm font-semibold text-blue-800">Regla activa: Solo clientes autorizados</p>
        <p class="text-xs text-blue-700 mt-1">
            Solo se asignarán clientes que <strong>ya autorizaron</strong> el tratamiento de sus datos personales (Ley 1581).
            Los clientes sin autorización son excluidos automáticamente.
        </p>
    </div>
</div>
@endif

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
@endif

<script>
window._assignData = {
    previewUrl: @json(route('admin.campaigns.assign.preview', $campaign)),
    departments: @json($departments->map(fn($d) => ['id'=>$d->id,'name'=>$d->name])),
    cities: @json($cities->map(fn($c) => ['id'=>$c->id,'name'=>$c->name,'department_id'=>$c->department_id]))
};
</script>

<div class="grid grid-cols-3 gap-6">

    {{-- ── Columna izquierda: Segmentación ── --}}
    <div class="col-span-2" x-data="assignForm(window._assignData)">

        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Segmentación por ubicación</h2>

            <div class="grid grid-cols-2 gap-6">

                {{-- Departamentos --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Departamentos</label>
                        <div class="flex gap-2 text-xs">
                            <button type="button" @click="selectAllDepts()" class="text-blue-600 hover:text-blue-800">Todos</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" @click="clearDepts()" class="text-gray-500 hover:text-gray-700">Ninguno</button>
                        </div>
                    </div>

                    <div class="relative mb-2">
                        <input type="text" x-model="deptSearch" placeholder="Buscar departamento..."
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="border border-gray-200 rounded-lg max-h-64 overflow-y-auto divide-y divide-gray-50">
                        <template x-for="dept in filteredDepts" :key="dept.id">
                            <label class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox"
                                       :value="dept.id"
                                       :checked="selectedDepts.includes(dept.id)"
                                       @change="toggleDept(dept.id)"
                                       class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs text-gray-700" x-text="dept.name"></span>
                            </label>
                        </template>
                        <div x-show="filteredDepts.length === 0" class="px-3 py-4 text-xs text-gray-400 text-center">
                            Sin resultados
                        </div>
                    </div>
                    <p class="mt-1.5 text-[10px] text-gray-400" x-text="selectedDepts.length + ' seleccionados'"></p>
                </div>

                {{-- Ciudades --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">
                            Ciudades
                            <span x-show="selectedDepts.length === 0" class="text-gray-400 font-normal text-xs">(selecciona un depto.)</span>
                        </label>
                        <div class="flex gap-2 text-xs" x-show="selectedDepts.length > 0">
                            <button type="button" @click="selectAllCities()" class="text-blue-600 hover:text-blue-800">Todas</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" @click="clearCities()" class="text-gray-500 hover:text-gray-700">Ninguna</button>
                        </div>
                    </div>

                    <div class="relative mb-2" x-show="selectedDepts.length > 0">
                        <input type="text" x-model="citySearch" placeholder="Buscar ciudad..."
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="border border-gray-200 rounded-lg max-h-64 overflow-y-auto divide-y divide-gray-50"
                         :class="selectedDepts.length === 0 ? 'bg-gray-50' : ''">
                        <template x-if="selectedDepts.length === 0">
                            <div class="px-3 py-8 text-xs text-gray-400 text-center">
                                Selecciona al menos un departamento para ver sus ciudades
                            </div>
                        </template>
                        <template x-if="selectedDepts.length > 0">
                            <div>
                                <template x-for="city in filteredCities" :key="city.id">
                                    <label class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox"
                                               :value="city.id"
                                               :checked="selectedCities.includes(city.id)"
                                               @change="toggleCity(city.id)"
                                               class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs text-gray-700" x-text="city.name"></span>
                                    </label>
                                </template>
                                <div x-show="filteredCities.length === 0" class="px-3 py-4 text-xs text-gray-400 text-center">
                                    Sin ciudades para los departamentos seleccionados
                                </div>
                            </div>
                        </template>
                    </div>
                    <p class="mt-1.5 text-[10px] text-gray-400" x-text="selectedCities.length > 0 ? selectedCities.length + ' ciudades seleccionadas' : (selectedDepts.length > 0 ? 'Sin filtro de ciudad — se incluirán todas las del depto.' : '')"></p>
                </div>
            </div>
        </div>

        {{-- Vista previa --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 mb-1">Vista previa</h2>
                    <p class="text-xs text-gray-500">
                        Consulta cuántos clientes se asignarán antes de confirmar.
                        <span x-show="selectedDepts.length === 0 && selectedCities.length === 0" class="text-amber-600">
                            — Sin filtros de ubicación se incluirán clientes de todas las ciudades.
                        </span>
                    </p>
                </div>
                <button type="button" @click="fetchPreview()"
                        class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Previsualizar
                </button>
            </div>

            <div x-show="preview !== null" class="mt-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-center">
                        <p class="text-3xl font-bold text-green-700" x-text="preview?.count ?? '—'"></p>
                        <p class="text-xs text-green-600 mt-1">Clientes nuevos a asignar</p>
                    </div>
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                        <p class="text-3xl font-bold text-gray-600" x-text="preview?.existing ?? '—'"></p>
                        <p class="text-xs text-gray-500 mt-1">Ya asignados (se omitirán)</p>
                    </div>
                </div>

                <div x-show="preview?.count === 0" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    No hay clientes que cumplan los criterios. Intenta con otros filtros de ubicación.
                </div>
            </div>

            {{-- Formulario de asignación --}}
            <form method="POST" action="{{ route('admin.campaigns.assign.store', $campaign) }}" class="mt-5">
                @csrf
                <template x-for="id in selectedDepts" :key="'d'+id">
                    <input type="hidden" name="department_ids[]" :value="id">
                </template>
                <template x-for="id in selectedCities" :key="'c'+id">
                    <input type="hidden" name="city_ids[]" :value="id">
                </template>

                <div class="flex gap-3">
                    <button type="submit"
                            :disabled="preview === null || preview.count === 0"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        <span x-show="preview === null">Previsualizar antes de asignar</span>
                        <span x-show="preview !== null && preview.count === 0">Sin clientes que asignar</span>
                        <span x-show="preview !== null && preview.count > 0">
                            Asignar <span x-text="preview.count"></span> clientes a la campaña
                        </span>
                    </button>
                    <a href="{{ route('admin.campaigns.show', $campaign) }}"
                       class="px-5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Columna derecha: Importar CSV ── --}}
    <div class="col-span-1 space-y-4">

        {{-- Import CSV --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Importar clientes CSV</h2>
            <p class="text-xs text-gray-500 mb-4">
                ¿Tienes clientes nuevos? Importa primero el CSV para registrarlos en el sistema.
                Luego usa los filtros para asignarlos a la campaña.
            </p>

            @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="mb-3 bg-amber-50 border border-amber-200 rounded-lg p-3">
                <p class="text-xs font-semibold text-amber-800 mb-1">Filas con problemas:</p>
                <ul class="text-[10px] text-amber-700 space-y-0.5 max-h-24 overflow-y-auto">
                    @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.campaigns.import', $campaign) }}"
                  enctype="multipart/form-data" x-data="csvForm()">
                @csrf

                <div class="border-2 border-dashed rounded-xl p-5 text-center transition-colors cursor-pointer mb-3"
                     :class="file ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                     @click="$refs.csvInput.click()"
                     @dragover.prevent
                     @drop.prevent="handleDrop($event)">
                    <input type="file" name="file" x-ref="csvInput" class="sr-only"
                           accept=".csv,.txt,.xlsx,.xls" @change="handleFile($event)">
                    <template x-if="!file">
                        <div>
                            <div class="text-3xl mb-2">📂</div>
                            <p class="text-xs font-medium text-gray-700">Clic o arrastra el archivo</p>
                            <p class="text-[10px] text-gray-400 mt-1">CSV, XLSX — máx. 5 MB</p>
                        </div>
                    </template>
                    <template x-if="file">
                        <div>
                            <div class="text-3xl mb-2">✅</div>
                            <p class="text-xs font-semibold text-gray-800" x-text="file.name"></p>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="formatSize(file.size)"></p>
                            <button type="button" @click.stop="clearFile()" class="text-[10px] text-red-500 hover:text-red-700 mt-1">Cambiar</button>
                        </div>
                    </template>
                </div>

                <button type="submit" :disabled="!file"
                        class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2 rounded-lg text-xs font-semibold transition-colors">
                    Importar clientes
                </button>
            </form>
        </div>

        {{-- Columnas del CSV --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-800">Columnas del archivo</h2>
                <a href="{{ route('admin.campaigns.import.template') }}"
                   class="text-[10px] text-blue-600 hover:text-blue-800 font-medium">
                    Descargar plantilla
                </a>
            </div>
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wide text-gray-400 border-b border-gray-100">
                        <th class="text-left pb-2">Columna</th>
                        <th class="text-left pb-2">Req.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr>
                        <td class="py-1.5">
                            <code class="bg-gray-100 px-1 rounded text-[10px]">celular</code>
                            <code class="bg-gray-100 px-1 rounded text-[10px]">telefono</code>
                        </td>
                        <td class="py-1.5 text-red-500 font-bold">Sí</td>
                    </tr>
                    <tr>
                        <td class="py-1.5"><code class="bg-gray-100 px-1 rounded text-[10px]">nombre</code></td>
                        <td class="py-1.5 text-gray-400">No</td>
                    </tr>
                    <tr>
                        <td class="py-1.5"><code class="bg-gray-100 px-1 rounded text-[10px]">email</code> <code class="bg-gray-100 px-1 rounded text-[10px]">correo</code></td>
                        <td class="py-1.5 text-gray-400">No</td>
                    </tr>
                    <tr>
                        <td class="py-1.5"><code class="bg-blue-100 px-1 rounded text-[10px] text-blue-700">departamento</code></td>
                        <td class="py-1.5 text-gray-400">No</td>
                    </tr>
                    <tr>
                        <td class="py-1.5"><code class="bg-blue-100 px-1 rounded text-[10px] text-blue-700">ciudad</code></td>
                        <td class="py-1.5 text-gray-400">No</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-2 text-[10px] text-gray-400">
                Las columnas <span class="text-blue-600 font-medium">departamento</span> y
                <span class="text-blue-600 font-medium">ciudad</span> permiten mejor segmentación
                al usar los filtros de arriba.
            </p>
        </div>

        {{-- Info --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs text-gray-500 space-y-2">
            <p class="font-medium text-gray-700">¿Cómo funciona?</p>
            <p>1. <strong>Importa</strong> el CSV si tienes clientes nuevos → se crean en el sistema.</p>
            <p>2. <strong>Selecciona</strong> los departamentos y ciudades que quieres incluir.</p>
            <p>3. <strong>Previsualiza</strong> cuántos clientes van a quedar asignados.</p>
            <p>4. <strong>Asigna</strong> con un clic. La regla de autorización se aplica automáticamente.</p>
        </div>
    </div>

</div>

<script>
function assignForm({ previewUrl, departments, cities }) {
    return {
        selectedDepts: [],
        selectedCities: [],
        deptSearch: '',
        citySearch: '',
        preview: null,
        loading: false,

        get filteredDepts() {
            if (!this.deptSearch) return departments;
            const s = this.deptSearch.toLowerCase();
            return departments.filter(d => d.name.toLowerCase().includes(s));
        },

        get filteredCities() {
            const deptCities = cities.filter(c => this.selectedDepts.includes(c.department_id));
            if (!this.citySearch) return deptCities;
            const s = this.citySearch.toLowerCase();
            return deptCities.filter(c => c.name.toLowerCase().includes(s));
        },

        toggleDept(id) {
            if (this.selectedDepts.includes(id)) {
                this.selectedDepts = this.selectedDepts.filter(d => d !== id);
                // Quitar ciudades que ya no aplican
                const remaining = cities.filter(c => this.selectedDepts.includes(c.department_id)).map(c => c.id);
                this.selectedCities = this.selectedCities.filter(c => remaining.includes(c));
            } else {
                this.selectedDepts.push(id);
            }
            this.preview = null;
        },

        toggleCity(id) {
            if (this.selectedCities.includes(id)) {
                this.selectedCities = this.selectedCities.filter(c => c !== id);
            } else {
                this.selectedCities.push(id);
            }
            this.preview = null;
        },

        selectAllDepts() {
            this.selectedDepts = departments.map(d => d.id);
            this.preview = null;
        },

        clearDepts() {
            this.selectedDepts = [];
            this.selectedCities = [];
            this.preview = null;
        },

        selectAllCities() {
            this.selectedCities = this.filteredCities.map(c => c.id);
            this.preview = null;
        },

        clearCities() {
            this.selectedCities = [];
            this.preview = null;
        },

        async fetchPreview() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                this.selectedDepts.forEach(id => params.append('department_ids[]', id));
                this.selectedCities.forEach(id => params.append('city_ids[]', id));
                const resp = await fetch(`${previewUrl}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.preview = await resp.json();
            } catch(e) {
                alert('Error al obtener la vista previa. Intenta de nuevo.');
            } finally {
                this.loading = false;
            }
        }
    }
}

function csvForm() {
    return {
        file: null,
        handleFile(e) { const f = e.target.files[0]; if (f) this.file = f; },
        handleDrop(e) {
            const f = e.dataTransfer.files[0];
            if (f) {
                this.file = f;
                const dt = new DataTransfer(); dt.items.add(f);
                this.$refs.csvInput.files = dt.files;
            }
        },
        clearFile() { this.file = null; this.$refs.csvInput.value = ''; },
        formatSize(b) {
            if (b < 1024) return b + ' B';
            if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
            return (b/1048576).toFixed(1) + ' MB';
        }
    }
}
</script>

@endsection
