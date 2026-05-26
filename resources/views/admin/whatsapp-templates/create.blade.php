@extends('layouts.admin')
@section('title', 'Nueva Plantilla WhatsApp')
@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
    <a href="{{ route('admin.whatsapp-campaigns.index') }}" class="hover:text-gray-600">Campañas WhatsApp</a>
    <span>/</span>
    <a href="{{ route('admin.whatsapp-templates.index') }}" class="hover:text-gray-600">Plantillas</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Nueva plantilla</span>
</div>

<div class="max-w-2xl" x-data="tplCreate()" x-init="init()">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nueva Plantilla WhatsApp</h1>
        <p class="text-sm text-gray-500 mt-1">Las plantillas se someten a Meta para aprobación (24-48 h). Solo se pueden usar en campañas masivas las plantillas <strong>APPROVED</strong>.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if($driver !== 'zenvia')
        <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
            <strong>Driver WhatsApp no es Zenvia.</strong>
            Configura las credenciales en <a href="{{ route('admin.providers.index') }}" class="underline">Proveedores</a> antes de crear plantillas.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.whatsapp-templates.store') }}">
        @csrf

        {{-- 1: Datos básicos --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">1. Información básica</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la plantilla <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="ej: promo_descuento_mayo"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-0.5">Solo letras minúsculas, números y guiones bajos. Sin espacios ni mayúsculas.</p>
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Idioma <span class="text-red-500">*</span></label>
                        <select name="locale" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            @foreach(['es'=>'Español (es)','es_MX'=>'Español México (es_MX)','es_AR'=>'Español Argentina (es_AR)','es_CO'=>'Español Colombia (es_CO)','en'=>'English (en)','en_US'=>'English US (en_US)','pt_BR'=>'Português Brasil (pt_BR)','fr'=>'Français (fr)'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ old('locale','es') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('locale')<p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría <span class="text-red-500">*</span></label>
                        <select name="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="MARKETING" {{ old('category','MARKETING') === 'MARKETING' ? 'selected' : '' }}>MARKETING — Promos, cupones, ofertas</option>
                            <option value="UTILITY"   {{ old('category') === 'UTILITY'   ? 'selected' : '' }}>UTILITY — Confirmaciones, notificaciones</option>
                            <option value="AUTHENTICATION" {{ old('category') === 'AUTHENTICATION' ? 'selected' : '' }}>AUTHENTICATION — OTP, verificación</option>
                        </select>
                        @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2: Encabezado --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700">2. Encabezado <span class="text-gray-400 font-normal">(opcional)</span></h2>
                    <p class="text-xs text-gray-400 mt-0.5">Texto o media que aparece antes del cuerpo del mensaje.</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" x-model="hasHeader" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-checked:bg-green-500 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                </label>
            </div>

            <div x-show="hasHeader" x-transition class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de encabezado</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['TEXT'=>['📝','Texto'],'IMAGE'=>['🖼','Imagen'],'VIDEO'=>['🎬','Video'],'DOCUMENT'=>['📄','Documento']] as $type=>[$icon,$label])
                            <label class="relative cursor-pointer">
                                <input type="radio" name="header_type" value="{{ $type }}"
                                       x-model="headerType" class="sr-only peer">
                                <div class="border-2 rounded-xl p-3 text-center transition-all peer-checked:border-green-500 peer-checked:bg-green-50 border-gray-200 hover:border-gray-300">
                                    <div class="text-lg">{{ $icon }}</div>
                                    <p class="text-xs font-medium text-gray-700 mt-1">{{ $label }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="headerType === 'TEXT'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Texto del encabezado</label>
                    <input type="text" name="header_text" value="{{ old('header_text') }}" maxlength="60"
                           placeholder="ej: 🎉 Oferta especial para ti"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('header_text') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-0.5">Máximo 60 caracteres. Puede incluir una variable <code class="bg-gray-100 px-1 rounded">@{{1}}</code>.</p>
                    @error('header_text')<p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div x-show="headerType !== 'TEXT' && headerType !== ''">
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL de ejemplo <span class="text-gray-400 text-xs">(requerida por Meta para aprobación)</span></label>
                    <input type="url" name="header_media_url" value="{{ old('header_media_url') }}"
                           placeholder="https://ejemplo.com/imagen.jpg"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('header_media_url') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-0.5">URL pública accesible. Se usa solo como ejemplo de revisión; el archivo real se envía al crear la campaña.</p>
                    @error('header_media_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- 3: Cuerpo --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">3. Cuerpo del mensaje <span class="text-red-500">*</span></h2>
            <p class="text-xs text-gray-500 mb-4">
                Usa <code class="bg-gray-100 px-1 rounded">@{{1}}</code>, <code class="bg-gray-100 px-1 rounded">@{{2}}</code>, etc. para variables dinámicas.
                También puedes usar <strong>*negrita*</strong>, _cursiva_, ~tachado~.
            </p>

            <div class="mb-3">
                <div class="flex gap-2 flex-wrap mb-2">
                    @foreach(['@{{1}}','@{{2}}','@{{3}}','*texto*','_texto_'] as $snippet)
                        <button type="button" @click="insertSnippet('{{ $snippet }}')"
                                class="text-xs bg-gray-100 hover:bg-green-100 hover:text-green-700 text-gray-700 px-2 py-1 rounded font-mono transition-colors">{{ $snippet }}</button>
                    @endforeach
                </div>
                <textarea name="body" id="tpl-body" rows="5"
                          x-model="body"
                          @input="onBodyInput()"
                          placeholder="Hola @{{1}}, tu código de descuento es *@{{2}}* con @{{3}} de descuento. Válido hasta el 31 de diciembre."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                <div class="flex justify-between mt-1">
                    <span class="text-xs text-gray-400"><span x-text="bodyVars.length"></span> variable(s) detectada(s)</span>
                    <span class="text-xs font-mono text-gray-400"><span x-text="body.length"></span>/1024</span>
                </div>
                @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Ejemplos de variables --}}
            <div x-show="bodyVars.length > 0" x-transition>
                <p class="text-xs font-semibold text-gray-600 mb-2">
                    Valores de ejemplo para las variables <span class="text-gray-400 font-normal">(requeridos por Meta)</span>
                </p>
                <div class="space-y-2">
                    <template x-for="(v, i) in bodyVars" :key="v">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono bg-green-50 text-green-700 px-2 py-1 rounded border border-green-100 w-14 text-center flex-shrink-0" x-text="'{{' + v + '}}'"></span>
                            <input type="text"
                                   :name="'body_examples[' + i + ']'"
                                   :placeholder="'Ej: ' + (i === 0 ? 'Juan García' : i === 1 ? 'PROMO25' : '20%')"
                                   class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- 4: Pie de página --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700">4. Pie de página <span class="text-gray-400 font-normal">(opcional)</span></h2>
                    <p class="text-xs text-gray-400 mt-0.5">Texto pequeño debajo del cuerpo. No admite variables.</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" x-model="hasFooter" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-checked:bg-green-500 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                </label>
            </div>
            <div x-show="hasFooter" x-transition>
                <input type="text" name="footer" value="{{ old('footer') }}" maxlength="60"
                       placeholder="ej: Válido hasta el 31 de diciembre · T&C aplican"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('footer') border-red-400 @enderror">
                @error('footer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- 5: Botones --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700">5. Botones <span class="text-gray-400 font-normal">(opcional, máx. 3)</span></h2>
                    <p class="text-xs text-gray-400 mt-0.5">Respuesta rápida, URL o número de teléfono.</p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" x-model="hasButtons" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-checked:bg-green-500 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                </label>
            </div>

            <div x-show="hasButtons" x-transition class="space-y-3">
                <template x-for="(btn, idx) in buttons" :key="idx">
                    <div class="border border-gray-200 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="'Botón ' + (idx + 1)"></span>
                            <button type="button" @click="removeButton(idx)"
                                    class="text-red-400 hover:text-red-600 text-xs px-2 py-1 rounded hover:bg-red-50 transition-colors">
                                Eliminar
                            </button>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['QUICK_REPLY'=>['💬','Respuesta rápida'],'URL'=>['🔗','URL'],'PHONE_NUMBER'=>['📞','Teléfono']] as $btype=>[$bicon,$blabel])
                                <label class="relative cursor-pointer">
                                    <input type="radio" :name="'buttons[' + idx + '][type]'" value="{{ $btype }}"
                                           x-model="btn.type" class="sr-only peer">
                                    <div class="border-2 rounded-lg p-2 text-center transition-all peer-checked:border-green-500 peer-checked:bg-green-50 border-gray-200 hover:border-gray-300">
                                        <div class="text-base">{{ $bicon }}</div>
                                        <p class="text-[10px] font-medium text-gray-700 mt-0.5">{{ $blabel }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Texto del botón <span class="text-red-500">*</span> <span class="text-gray-400">(máx. 25 chars)</span></label>
                            <input type="text" :name="'buttons[' + idx + '][text]'" x-model="btn.text"
                                   maxlength="25" placeholder="ej: Usar ahora"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>

                        <div x-show="btn.type === 'URL'">
                            <label class="block text-xs font-medium text-gray-600 mb-1">URL <span class="text-red-500">*</span></label>
                            <input type="url" :name="'buttons[' + idx + '][url]'" x-model="btn.url"
                                   placeholder="https://ejemplo.com/promo/{{1}}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                            <p class="text-xs text-gray-400 mt-0.5">Puede incluir <code class="bg-gray-100 px-0.5 rounded">@{{1}}</code> como variable dinámica.</p>
                        </div>

                        <div x-show="btn.type === 'PHONE_NUMBER'">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Número de teléfono <span class="text-red-500">*</span></label>
                            <input type="text" :name="'buttons[' + idx + '][phone_number]'" x-model="btn.phone_number"
                                   placeholder="+573001234567"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        </div>

                        <input type="hidden" :name="'buttons[' + idx + '][type]'" :value="btn.type">
                    </div>
                </template>

                <button type="button" @click="addButton()"
                        x-show="buttons.length < 3"
                        class="w-full border-2 border-dashed border-gray-200 hover:border-green-400 text-gray-500 hover:text-green-600 rounded-xl py-3 text-sm font-medium transition-colors">
                    + Añadir botón
                </button>
            </div>
        </div>

        {{-- 6: Preview --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">6. Vista previa del payload</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Mockup visual --}}
                <div>
                    <p class="text-xs text-gray-500 mb-2">Aspecto aproximado en WhatsApp</p>
                    <div class="bg-[#e5ddd5] rounded-xl p-3 min-h-32">
                        <div class="bg-white rounded-lg p-3 shadow-sm max-w-xs">
                            <template x-if="hasHeader && headerType === 'TEXT' && headerText.length > 0">
                                <p class="text-sm font-bold text-gray-900 mb-1" x-text="headerText"></p>
                            </template>
                            <template x-if="hasHeader && headerType !== 'TEXT'">
                                <div class="bg-gray-100 rounded h-16 flex items-center justify-center mb-2">
                                    <span class="text-2xl" x-text="headerType === 'IMAGE' ? '🖼' : headerType === 'VIDEO' ? '🎬' : '📄'"></span>
                                </div>
                            </template>
                            <p class="text-xs text-gray-800 whitespace-pre-wrap leading-relaxed"
                               x-text="body || '(escribe el cuerpo del mensaje)'"></p>
                            <template x-if="hasFooter && footerText.length > 0">
                                <p class="text-[10px] text-gray-400 mt-1" x-text="footerText"></p>
                            </template>
                            <p class="text-right text-[10px] text-gray-400 mt-1">✓✓</p>
                        </div>
                        <template x-if="hasButtons && buttons.length > 0">
                            <div class="mt-1 space-y-1 max-w-xs">
                                <template x-for="btn in buttons" :key="btn.text">
                                    <div class="bg-white rounded-lg py-2 text-center shadow-sm">
                                        <span class="text-xs text-blue-600 font-medium"
                                              x-text="(btn.type==='URL'?'🔗 ':btn.type==='PHONE_NUMBER'?'📞 ':'💬 ') + (btn.text || 'Botón')"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                {{-- JSON payload --}}
                <div>
                    <p class="text-xs text-gray-500 mb-2">JSON que se enviará a Zenvia</p>
                    <pre class="text-[10px] text-gray-700 font-mono bg-gray-50 border border-gray-100 rounded-xl p-3 overflow-x-auto max-h-64 overflow-y-auto whitespace-pre-wrap"
                         x-text="buildPreview()"></pre>
                </div>
            </div>
        </div>

        @if($driver !== 'zenvia')
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-800">
                ⚠️ El driver activo no es Zenvia. La plantilla no se podrá crear hasta configurar las credenciales.
            </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" @if($driver !== 'zenvia') disabled @endif
                    class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                Enviar a Meta para aprobación
            </button>
            <a href="{{ route('admin.whatsapp-templates.index') }}"
               class="flex-1 text-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 py-2.5 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function tplCreate() {
    return {
        // Header
        hasHeader:  false,
        headerType: 'TEXT',
        headerText: '',

        // Body
        body:     @json(old('body', '')),
        bodyVars: [],

        // Footer
        hasFooter: false,
        footerText: '',

        // Buttons
        hasButtons: false,
        buttons: [],

        init() {
            this.onBodyInput();
            // Restore old values
            @if(old('header_type'))
                this.hasHeader  = true;
                this.headerType = @json(old('header_type', 'TEXT'));
                this.headerText = @json(old('header_text', ''));
            @endif
            @if(old('footer'))
                this.hasFooter  = true;
                this.footerText = @json(old('footer', ''));
            @endif
            @if(old('buttons'))
                this.hasButtons = true;
                @php
                    $oldBtns = collect(old('buttons', []))->map(fn($b) => [
                        'type'         => $b['type'] ?? 'QUICK_REPLY',
                        'text'         => $b['text'] ?? '',
                        'url'          => $b['url'] ?? '',
                        'phone_number' => $b['phone_number'] ?? '',
                    ])->values()->toArray();
                @endphp
                this.buttons = {!! json_encode($oldBtns) !!};
            @endif
        },

        onBodyInput() {
            const matches = [...(this.body.matchAll(/\{\{(\w+)\}\}/g))];
            const seen = new Set();
            this.bodyVars = [];
            for (const m of matches) {
                if (!seen.has(m[1])) { seen.add(m[1]); this.bodyVars.push(m[1]); }
            }
        },

        insertSnippet(s) {
            const ta = document.getElementById('tpl-body');
            if (!ta) return;
            const start = ta.selectionStart ?? this.body.length;
            const end   = ta.selectionEnd   ?? this.body.length;
            this.body = this.body.substring(0, start) + s + this.body.substring(end);
            this.$nextTick(() => { ta.selectionStart = ta.selectionEnd = start + s.length; ta.focus(); this.onBodyInput(); });
        },

        addButton() {
            if (this.buttons.length < 3) {
                this.buttons.push({ type: 'QUICK_REPLY', text: '', url: '', phone_number: '' });
            }
        },

        removeButton(i) { this.buttons.splice(i, 1); },

        buildPreview() {
            const components = [];

            if (this.hasHeader && this.headerType) {
                const h = { type: 'HEADER', format: this.headerType };
                if (this.headerType === 'TEXT') h.text = this.headerText || '';
                else h.example = { header_handle: ['https://...'] };
                components.push(h);
            }

            const bComp = { type: 'BODY', text: this.body };
            if (this.bodyVars.length > 0) bComp.example = { body_text: [this.bodyVars.map((_, i) => 'Ejemplo' + (i+1))] };
            components.push(bComp);

            if (this.hasFooter && this.footerText) components.push({ type: 'FOOTER', text: this.footerText });

            const btns = this.buttons.filter(b => b.type && b.text);
            if (this.hasButtons && btns.length > 0) {
                const built = btns.map(b => {
                    const o = { type: b.type, text: b.text };
                    if (b.type === 'URL' && b.url) o.url = b.url;
                    if (b.type === 'PHONE_NUMBER' && b.phone_number) o.phone_number = b.phone_number;
                    return o;
                });
                components.push({ type: 'BUTTONS', buttons: built });
            }

            return JSON.stringify({ name: '...', locale: '...', channel: 'whatsapp', category: '...', components }, null, 2);
        },
    }
}
</script>

@endsection
