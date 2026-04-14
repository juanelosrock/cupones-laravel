@extends('layouts.admin')
@section('title', $config ? 'Editar landing' : 'Nueva landing')
@section('content')

{{-- CDN Quill --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css">

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.landing-configs.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← Landing Pages</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">{{ $config ? 'Editar landing' : 'Nueva landing' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
    <p class="text-sm font-semibold text-red-800 mb-1">Corrige los siguientes errores</p>
    @foreach($errors->all() as $e)
        <p class="text-xs text-red-700">{{ $e }}</p>
    @endforeach
</div>
@endif

<form method="POST"
      action="{{ $config ? route('admin.landing-configs.update', $config) : route('admin.landing-configs.store') }}"
      enctype="multipart/form-data"
      x-data="landingForm()"
      x-init="init()">
    @csrf
    @if($config) @method('PUT') @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ── COLUMNA IZQUIERDA: Config + Contenido ── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Nombre --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <label class="block text-sm font-semibold text-gray-800 mb-1">Nombre interno <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       value="{{ old('name', $config?->name) }}"
                       placeholder="Ej: Landing Navidad 2026 — Colores corporativos"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                <p class="text-xs text-gray-400 mt-1">Solo visible en el panel admin, no para los clientes.</p>
            </div>

            {{-- Template picker --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-800 mb-3">Plantilla visual <span class="text-red-500">*</span></p>
                <div class="grid grid-cols-3 gap-3">

                    {{-- Minimal --}}
                    <label class="cursor-pointer group" @click="selectedTemplate='minimal'">
                        <input type="radio" name="template" value="minimal" class="sr-only"
                               {{ old('template', $config?->template ?? 'minimal') === 'minimal' ? 'checked' : '' }}>
                        <div class="rounded-xl border-2 transition-all overflow-hidden"
                             :class="selectedTemplate==='minimal' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 group-hover:border-gray-300'">
                            {{-- Preview --}}
                            <div class="bg-gray-50 p-2 h-28 flex items-center justify-center">
                                <div class="bg-white rounded-lg shadow-sm w-full h-full p-2 flex flex-col justify-between">
                                    <div>
                                        <div class="w-10 h-1.5 bg-blue-400 rounded-full mb-1"></div>
                                        <div class="w-14 h-1 bg-gray-200 rounded-full mb-0.5"></div>
                                        <div class="w-12 h-1 bg-gray-200 rounded-full"></div>
                                    </div>
                                    <div class="w-full h-5 bg-blue-500 rounded-md"></div>
                                </div>
                            </div>
                            <div class="p-2 text-center border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-700">Minimal</p>
                                <p class="text-[10px] text-gray-400">Fondo claro, tarjeta centrada</p>
                            </div>
                        </div>
                    </label>

                    {{-- Branded --}}
                    <label class="cursor-pointer group" @click="selectedTemplate='branded'">
                        <input type="radio" name="template" value="branded" class="sr-only"
                               {{ old('template', $config?->template) === 'branded' ? 'checked' : '' }}>
                        <div class="rounded-xl border-2 transition-all overflow-hidden"
                             :class="selectedTemplate==='branded' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 group-hover:border-gray-300'">
                            <div class="h-28 flex flex-col overflow-hidden">
                                <div class="h-10 flex items-center justify-center" :style="`background:${brandColor}`">
                                    <div class="w-8 h-2 bg-white rounded-full opacity-80"></div>
                                </div>
                                <div class="flex-1 bg-white p-2 flex flex-col justify-between">
                                    <div>
                                        <div class="w-10 h-1.5 bg-gray-300 rounded-full mb-1"></div>
                                        <div class="w-14 h-1 bg-gray-200 rounded-full"></div>
                                    </div>
                                    <div class="w-full h-4 rounded" :style="`background:${brandColor}`"></div>
                                </div>
                            </div>
                            <div class="p-2 text-center border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-700">Branded</p>
                                <p class="text-[10px] text-gray-400">Header de color con logo</p>
                            </div>
                        </div>
                    </label>

                    {{-- Hero --}}
                    <label class="cursor-pointer group" @click="selectedTemplate='hero'">
                        <input type="radio" name="template" value="hero" class="sr-only"
                               {{ old('template', $config?->template) === 'hero' ? 'checked' : '' }}>
                        <div class="rounded-xl border-2 transition-all overflow-hidden"
                             :class="selectedTemplate==='hero' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 group-hover:border-gray-300'">
                            <div class="h-28 relative overflow-hidden flex items-center justify-center"
                                 style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);">
                                <div class="absolute inset-0 opacity-10" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><circle cx=%2230%22 cy=%2230%22 r=%2228%22 fill=%22white%22/></svg>') center/cover"></div>
                                <div class="relative bg-white/20 backdrop-blur-sm rounded-lg p-2 w-20">
                                    <div class="w-10 h-1.5 bg-white rounded-full mb-1 mx-auto"></div>
                                    <div class="w-12 h-1 bg-white/60 rounded-full mb-0.5 mx-auto"></div>
                                    <div class="w-full h-4 bg-white rounded mt-1.5"></div>
                                </div>
                            </div>
                            <div class="p-2 text-center border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-700">Hero</p>
                                <p class="text-[10px] text-gray-400">Imagen de fondo, formulario flotante</p>
                            </div>
                        </div>
                    </label>

                </div>
            </div>

            {{-- Textos --}}
            <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                <p class="text-sm font-semibold text-gray-800 border-b border-gray-100 pb-3">Textos del formulario</p>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Título principal <span class="text-red-500">*</span></label>
                    <input type="text" name="heading" required
                           value="{{ old('heading', $config?->heading ?? 'Autorización de datos personales') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Subtítulo</label>
                    <input type="text" name="subheading"
                           value="{{ old('subheading', $config?->subheading) }}"
                           placeholder="Ej: Para revelarte tu código necesitamos tu autorización"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">Cuerpo del mensaje (HTML)</label>
                    {{-- Quill editor --}}
                    <div id="quill-editor" class="min-h-[140px] bg-white">{!! old('body_html', $config?->body_html) !!}</div>
                    <input type="hidden" name="body_html" id="body_html_input">
                    <p class="text-xs text-gray-400 mt-1">Aparece encima de los checkboxes de aceptación. Puedes formatear el texto, añadir listas, etc.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Texto del botón principal <span class="text-red-500">*</span></label>
                    <input type="text" name="button_text" required
                           value="{{ old('button_text', $config?->button_text ?? 'Aceptar y ver mi código') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                </div>
            </div>

            {{-- Textos pantalla de éxito --}}
            <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                <p class="text-sm font-semibold text-gray-800 border-b border-gray-100 pb-3">Pantalla de confirmación</p>
                <p class="text-xs text-gray-500">Lo que ve el cliente después de aceptar.</p>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Título de confirmación <span class="text-red-500">*</span></label>
                    <input type="text" name="success_heading" required
                           value="{{ old('success_heading', $config?->success_heading ?? '¡Autorización registrada!') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Texto de confirmación <span class="text-red-500">*</span></label>
                    <textarea name="success_text" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none resize-none">{{ old('success_text', $config?->success_text ?? 'Tu consentimiento fue guardado correctamente.') }}</textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <label class="block text-xs font-medium text-gray-700 mb-1">Texto del pie de página</label>
                <input type="text" name="footer_text"
                       value="{{ old('footer_text', $config?->footer_text) }}"
                       placeholder="Ej: © 2026 Mi Empresa · Todos los derechos reservados"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
            </div>

        </div>

        {{-- ── COLUMNA DERECHA: Branding + Imágenes ── --}}
        <div class="space-y-5">

            {{-- Colores --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-800 mb-4">Colores</p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Color de marca (botones, acentos)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="brand_color" id="brand_color"
                                   value="{{ old('brand_color', $config?->brand_color ?? '#2563eb') }}"
                                   x-model="brandColor"
                                   class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                            <input type="text" id="brand_color_text"
                                   x-model="brandColor"
                                   @input="syncColor('brand')"
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Color de fondo</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="bg_color" id="bg_color"
                                   value="{{ old('bg_color', $config?->bg_color ?? '#f1f5f9') }}"
                                   x-model="bgColor"
                                   class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                            <input type="text" id="bg_color_text"
                                   x-model="bgColor"
                                   @input="syncColor('bg')"
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logo --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-800 mb-1">Logo</p>
                <p class="text-xs text-gray-500 mb-3">PNG o SVG con fondo transparente recomendado. Máx. 2 MB.</p>

                @if($config?->logo_url)
                <div class="mb-3 p-3 bg-gray-50 rounded-lg flex items-center gap-3" x-show="keepLogo">
                    <img src="{{ $config->logo_url }}" class="h-10 object-contain" alt="logo actual">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-600 truncate">Logo actual</p>
                    </div>
                    <button type="button" @click="keepLogo=false" class="text-xs text-red-500 hover:text-red-700">Quitar</button>
                </div>
                <input type="hidden" name="keep_logo" :value="keepLogo ? '1' : '0'">
                @endif

                <div x-show="{{ $config?->logo_url ? '!keepLogo' : 'true' }}" class="space-y-2">
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center cursor-pointer hover:border-blue-300 transition-colors"
                         @click="$refs.logoInput.click()">
                        <input type="file" name="logo_file" accept="image/*" x-ref="logoInput" class="hidden"
                               @change="previewImage($event, 'logoPreview')">
                        <div x-show="!logoPreview" class="text-xs text-gray-400">
                            <svg class="w-5 h-5 mx-auto mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Subir imagen
                        </div>
                        <img x-show="logoPreview" :src="logoPreview" class="h-12 mx-auto object-contain" alt="">
                    </div>
                    <p class="text-center text-xs text-gray-400">— o pega una URL —</p>
                    <input type="url" name="logo_url_input"
                           value="{{ old('logo_url_input') }}"
                           placeholder="https://..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                </div>
            </div>

            {{-- Hero image --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-800 mb-1">Imagen de fondo
                    <span class="text-xs font-normal text-gray-400">(solo para template Hero)</span>
                </p>
                <p class="text-xs text-gray-500 mb-3">Recomendado: 1200×800px mínimo. Máx. 5 MB.</p>

                @if($config?->hero_image_url)
                <div class="mb-3 rounded-lg overflow-hidden relative" x-show="keepHero">
                    <img src="{{ $config->hero_image_url }}" class="w-full h-24 object-cover" alt="hero actual">
                    <button type="button" @click="keepHero=false"
                            class="absolute top-1 right-1 bg-black/50 text-white text-xs px-2 py-0.5 rounded hover:bg-black/70">
                        Quitar
                    </button>
                </div>
                <input type="hidden" name="keep_hero" :value="keepHero ? '1' : '0'">
                @endif

                <div x-show="{{ $config?->hero_image_url ? '!keepHero' : 'true' }}" class="space-y-2">
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center cursor-pointer hover:border-blue-300 transition-colors"
                         @click="$refs.heroInput.click()">
                        <input type="file" name="hero_file" accept="image/*" x-ref="heroInput" class="hidden"
                               @change="previewImage($event, 'heroPreview')">
                        <div x-show="!heroPreview">
                            <svg class="w-5 h-5 mx-auto mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-xs text-gray-400">Subir imagen</p>
                        </div>
                        <img x-show="heroPreview" :src="heroPreview" class="w-full h-20 object-cover rounded" alt="">
                    </div>
                    <p class="text-center text-xs text-gray-400">— o pega una URL —</p>
                    <input type="url" name="hero_image_url_input"
                           value="{{ old('hero_image_url_input') }}"
                           placeholder="https://..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                </div>
            </div>

            {{-- Default flag --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1"
                           {{ old('is_default', $config?->is_default) ? 'checked' : '' }}
                           class="mt-0.5 rounded border-gray-300 text-blue-600">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Usar como landing por defecto</p>
                        <p class="text-xs text-gray-500 mt-0.5">Se aplicará automáticamente a las campañas SMS que no tengan una landing asignada.</p>
                    </div>
                </label>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3">
                <a href="{{ route('admin.landing-configs.index') }}"
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    {{ $config ? 'Guardar cambios' : 'Crear landing' }}
                </button>
            </div>

        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
function landingForm() {
    return {
        selectedTemplate: '{{ old('template', $config?->template ?? 'minimal') }}',
        brandColor:  '{{ old('brand_color', $config?->brand_color ?? '#2563eb') }}',
        bgColor:     '{{ old('bg_color', $config?->bg_color ?? '#f1f5f9') }}',
        logoPreview:  null,
        heroPreview:  null,
        keepLogo: true,
        keepHero: true,

        init() {
            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Escribe aquí el cuerpo del mensaje que verán los clientes...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });
            // Sync to hidden input on form submit
            this.$el.addEventListener('submit', () => {
                document.getElementById('body_html_input').value = quill.getSemanticHTML();
            });
        },

        syncColor(type) {
            // text input → color picker sync happens via x-model
        },

        previewImage(event, target) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => { this[target] = e.target.result; };
            reader.readAsDataURL(file);
        },
    };
}
</script>

<style>
.ql-container { border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
.ql-toolbar { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; }
.ql-editor { min-height: 120px; font-size: 0.875rem; }
</style>

@endsection
