@extends('layouts.admin')
@section('title', 'API Tester')

@push('styles')
<style>
.tester-response pre { white-space: pre-wrap; word-break: break-all; }
.endpoint-item { transition: background .12s; }
.endpoint-item.active { background: rgba(255,255,255,.1); }
.endpoint-item:hover:not(.active) { background: rgba(255,255,255,.05); }
[x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="apiTester()" x-init="init()" class="flex flex-col h-[calc(100vh-88px)]">

    {{-- Header bar --}}
    <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.api-clients.docs') }}" class="text-gray-400 hover:text-gray-600 transition-colors text-sm">← Documentación</a>
            <span class="text-gray-300">/</span>
            <h1 class="text-xl font-bold text-gray-900">API Tester</h1>
            <span class="text-xs px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full font-medium">Las peticiones se envían desde tu navegador</span>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <span class="font-mono">{{ $baseUrl }}</span>
        </div>
    </div>

    {{-- Main layout --}}
    <div class="flex gap-4 flex-1 min-h-0">

        {{-- ── LEFT: Endpoint list + credentials ── --}}
        <div class="w-72 flex-shrink-0 bg-gray-900 rounded-xl flex flex-col overflow-hidden">

            {{-- Credentials --}}
            <div class="p-4 border-b border-gray-700/60">
                <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-2">Credenciales</p>

                @if($apiClients->count())
                <select x-model="quickSelect" @change="applyQuickSelect()"
                        class="w-full text-xs bg-gray-800 text-gray-300 border border-gray-700 rounded-lg px-2.5 py-1.5 mb-2 focus:outline-none focus:border-blue-500">
                    <option value="">— Seleccionar cliente —</option>
                    @foreach($apiClients as $c)
                    <option value="{{ $c->client_id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @endif

                <input x-model="clientId" type="text" placeholder="X-Client-Id"
                       class="w-full text-xs bg-gray-800 text-gray-200 border border-gray-700 rounded-lg px-2.5 py-1.5 mb-1.5 font-mono placeholder-gray-600 focus:outline-none focus:border-blue-500" />
                <div class="relative">
                    <input x-model="clientSecret" :type="showSecret ? 'text' : 'password'" placeholder="X-Client-Secret"
                           class="w-full text-xs bg-gray-800 text-gray-200 border border-gray-700 rounded-lg px-2.5 py-1.5 font-mono placeholder-gray-600 focus:outline-none focus:border-blue-500 pr-8" />
                    <button @click="showSecret = !showSecret" type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                        <svg x-show="!showSecret" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showSecret" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Endpoint list --}}
            <div class="flex-1 overflow-y-auto py-2">
                <template x-for="group in endpointGroups" :key="group.label">
                    <div class="mb-1">
                        <p class="text-[10px] uppercase tracking-widest text-gray-600 px-4 py-1.5" x-text="group.label"></p>
                        <template x-for="ep in group.endpoints" :key="ep.id">
                            <button @click="selectEndpoint(ep)"
                                    class="endpoint-item w-full text-left px-4 py-2 flex items-center gap-2.5 rounded-lg mx-1"
                                    :class="selectedEndpoint?.id === ep.id ? 'active' : ''"
                                    style="width: calc(100% - 8px)">
                                <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded flex-shrink-0"
                                      :class="methodBadge(ep.method)" x-text="ep.method"></span>
                                <span class="text-xs text-gray-300 truncate font-mono" x-text="ep.shortPath"></span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── RIGHT: Request + Response ── --}}
        <div class="flex-1 min-w-0 flex flex-col gap-3">

            {{-- Empty state --}}
            <div x-show="!selectedEndpoint" class="flex-1 flex items-center justify-center bg-white rounded-xl shadow-sm">
                <div class="text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm">Selecciona un endpoint para empezar</p>
                </div>
            </div>

            <template x-if="selectedEndpoint">
                <div class="flex flex-col gap-3 flex-1 min-h-0">

                    {{-- URL bar + Send --}}
                    <div class="bg-white rounded-xl shadow-sm p-4 flex-shrink-0">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-xs font-bold font-mono px-2.5 py-1.5 rounded-lg flex-shrink-0"
                                  :class="methodBadge(selectedEndpoint.method)" x-text="selectedEndpoint.method"></span>
                            <div class="flex-1 font-mono text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700 overflow-x-auto whitespace-nowrap" x-text="buildUrl()"></div>
                            {{-- Export button --}}
                            <button @click="openExport()" type="button"
                                    class="flex-shrink-0 flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border transition-colors hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                                <span class="text-gray-600">Exportar</span>
                            </button>
                            <button @click="send()" :disabled="loading"
                                    class="flex-shrink-0 flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-semibold rounded-lg transition-colors">
                                <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12z"></path>
                                </svg>
                                <span x-text="loading ? 'Enviando…' : 'Enviar'"></span>
                            </button>
                        </div>

                        {{-- Description --}}
                        <p class="text-xs text-gray-500 mb-3" x-text="selectedEndpoint.description"></p>

                        {{-- Auth notice --}}
                        <template x-if="!selectedEndpoint.requiresAuth">
                            <div class="text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-1.5 mb-3">
                                Endpoint público — no requiere X-Client-Id / X-Client-Secret
                            </div>
                        </template>

                        {{-- Tabs --}}
                        <div class="flex gap-1 mb-3">
                            <button @click="activeTab='params'" x-show="hasPathParams()"
                                    :class="activeTab==='params' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors">
                                Path params
                                <span class="ml-1 text-[10px] bg-blue-500 text-white rounded-full px-1.5 py-0.5"
                                      x-text="Object.keys(pathParams).length"></span>
                            </button>
                            <button @click="activeTab='body'" x-show="selectedEndpoint.method !== 'GET'"
                                    :class="activeTab==='body' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors">Body (JSON)</button>
                            <button @click="activeTab='headers'"
                                    :class="activeTab==='headers' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors">Headers</button>
                        </div>

                        {{-- Tab: Path Params --}}
                        <div x-show="activeTab==='params'">
                            <div class="space-y-2">
                                <template x-for="(val, key) in pathParams" :key="key">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-mono text-gray-500 w-32 flex-shrink-0" x-text="'{' + key + '}'"></span>
                                        <input :value="pathParams[key]" @input="pathParams[key] = $event.target.value"
                                               type="text"
                                               class="flex-1 text-xs font-mono bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-blue-400"
                                               :placeholder="'Valor para ' + key" />
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Tab: Body --}}
                        <div x-show="activeTab==='body' && selectedEndpoint.method !== 'GET'">
                            <div class="relative rounded-xl overflow-hidden" style="background:#0d1117">
                                <textarea x-model="body" rows="8" spellcheck="false"
                                          class="w-full text-xs font-mono rounded-xl p-4 focus:outline-none resize-y border-0"
                                          style="background:#0d1117; color:#e6edf3; caret-color:#58a6ff; outline:none;"
                                          placeholder="{ }"></textarea>
                                <button @click="formatBody()" type="button"
                                        class="absolute top-2 right-2 text-[10px] px-2 py-1 rounded transition-colors"
                                        style="background:#21262d; color:#8b949e; border:1px solid #30363d;">
                                    Formatear
                                </button>
                            </div>
                        </div>

                        {{-- Tab: Headers --}}
                        <div x-show="activeTab==='headers'">
                            <div class="bg-gray-50 rounded-lg border border-gray-100 overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-gray-100 border-b border-gray-200">
                                            <th class="px-4 py-2 text-left text-gray-500 font-semibold uppercase tracking-wider text-[10px] w-1/3">Header</th>
                                            <th class="px-4 py-2 text-left text-gray-500 font-semibold uppercase tracking-wider text-[10px]">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-600">Content-Type</td>
                                            <td class="px-4 py-2 font-mono text-gray-500">application/json</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-600">Accept</td>
                                            <td class="px-4 py-2 font-mono text-gray-500">application/json</td>
                                        </tr>
                                        <template x-if="selectedEndpoint.requiresAuth">
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-gray-600">X-Client-Id</td>
                                                <td class="px-4 py-2 font-mono text-blue-600" x-text="clientId || '(vacío)'"></td>
                                            </tr>
                                        </template>
                                        <template x-if="selectedEndpoint.requiresAuth">
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-gray-600">X-Client-Secret</td>
                                                <td class="px-4 py-2 font-mono text-blue-600" x-text="clientSecret ? '••••••••' : '(vacío)'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Response panel --}}
                    <div class="rounded-xl shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden" style="background:#0d1117; border:1px solid #30363d;">

                        {{-- Label --}}
                        <div class="flex items-center justify-between px-4 py-2 flex-shrink-0" style="background:#161b22; border-bottom:1px solid #30363d;">
                            <span class="text-xs font-semibold uppercase tracking-wider" style="color:#8b949e;">Respuesta</span>
                        </div>

                        {{-- Response empty state --}}
                        <div x-show="!response && !loading" class="flex-1 flex items-center justify-center">
                            <div class="text-center" style="color:#484f58;">
                                <svg class="w-8 h-8 mx-auto mb-2" style="opacity:.4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm">La respuesta aparecerá aquí</p>
                            </div>
                        </div>

                        {{-- Loading state --}}
                        <div x-show="loading" class="flex-1 flex items-center justify-center">
                            <div class="text-center" style="color:#8b949e;">
                                <svg class="w-8 h-8 mx-auto mb-2 animate-spin" style="color:#58a6ff;" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12z"></path>
                                </svg>
                                <p class="text-sm mt-2">Enviando petición…</p>
                            </div>
                        </div>

                        {{-- Response content --}}
                        <template x-if="response && !loading">
                            <div class="flex flex-col flex-1 min-h-0">
                                {{-- Status bar --}}
                                <div class="flex items-center gap-4 px-4 py-2.5 flex-shrink-0" style="background:#161b22; border-bottom:1px solid #30363d;">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full font-mono"
                                          :class="statusBadge(response.status)"
                                          x-text="response.status + ' ' + response.statusText"></span>
                                    <span class="text-xs" style="color:#8b949e;">
                                        <span class="font-semibold" style="color:#e6edf3;" x-text="response.time + ' ms'"></span> tiempo de respuesta
                                    </span>
                                    <div class="ml-auto flex gap-2">
                                        <button @click="copyResponse()" type="button"
                                                class="text-xs flex items-center gap-1 px-2.5 py-1 rounded-lg transition-colors"
                                                style="background:#21262d; color:#8b949e; border:1px solid #30363d;">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                                        </button>
                                    </div>
                                </div>
                                {{-- Body --}}
                                <div class="flex-1 overflow-auto tester-response">
                                    <pre class="text-xs font-mono p-4 leading-relaxed" style="color:#e6edf3;"
                                         x-text="response.body"></pre>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>
            </template>
        </div>
    </div>

    {{-- ── Export Modal ── --}}
    <div x-show="exportModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.6);"
         @keydown.escape.window="exportModal=false">

        <div @click.outside="exportModal=false"
             class="w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col"
             style="background:#161b22; border:1px solid #30363d; max-height:80vh;">

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-5 py-4 flex-shrink-0" style="border-bottom:1px solid #30363d;">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4" style="color:#58a6ff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <span class="text-sm font-semibold" style="color:#e6edf3;">Exportar petición</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-mono"
                          :class="methodBadge(selectedEndpoint?.method)"
                          x-text="selectedEndpoint?.method + ' ' + selectedEndpoint?.shortPath"></span>
                </div>
                <button @click="exportModal=false" type="button"
                        class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                        style="color:#8b949e;" onmouseover="this.style.background='#21262d'" onmouseout="this.style.background='transparent'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-1 px-5 pt-3 flex-shrink-0" style="border-bottom:1px solid #30363d; padding-bottom:0;">
                <button @click="exportTab='curl'"
                        class="text-xs font-mono px-4 py-2 rounded-t-lg transition-colors"
                        :style="exportTab==='curl'
                            ? 'background:#0d1117; color:#58a6ff; border:1px solid #30363d; border-bottom:1px solid #0d1117; margin-bottom:-1px;'
                            : 'color:#8b949e;'">
                    cURL
                </button>
                <button @click="exportTab='php'"
                        class="text-xs font-mono px-4 py-2 rounded-t-lg transition-colors"
                        :style="exportTab==='php'
                            ? 'background:#0d1117; color:#58a6ff; border:1px solid #30363d; border-bottom:1px solid #0d1117; margin-bottom:-1px;'
                            : 'color:#8b949e;'">
                    PHP (cURL)
                </button>
            </div>

            {{-- Code area --}}
            <div class="flex-1 overflow-auto relative" style="background:#0d1117;">
                <pre class="text-xs font-mono p-5 leading-relaxed"
                     style="color:#e6edf3; tab-size:2;"
                     x-text="exportTab === 'curl' ? generateCurl() : generatePhp()"></pre>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-5 py-3 flex-shrink-0" style="background:#161b22; border-top:1px solid #30363d;">
                <p class="text-xs" style="color:#484f58;">
                    <span x-show="exportTab==='curl'">Ejecuta en terminal o importa en Postman / Insomnia</span>
                    <span x-show="exportTab==='php'">Requiere la extensión <code style="color:#79c0ff;">ext-curl</code> (disponible por defecto en PHP)</span>
                </p>
                <button @click="copyExport()" type="button"
                        class="flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
                        :style="copiedExport
                            ? 'background:#238636; color:#fff;'
                            : 'background:#21262d; color:#c9d1d9; border:1px solid #30363d;'">
                    <svg x-show="!copiedExport" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <svg x-show="copiedExport" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="copiedExport ? '¡Copiado!' : 'Copiar código'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function apiTester() {
    return {
        baseUrl: '{{ $baseUrl }}',
        clientId: '',
        clientSecret: '',
        showSecret: false,
        quickSelect: '',
        selectedEndpoint: null,
        pathParams: {},
        body: '',
        loading: false,
        response: null,
        activeTab: 'body',
        copied: false,
        exportModal: false,
        exportTab: 'curl',
        copiedExport: false,

        endpointGroups: [
            {
                label: 'Cupones',
                endpoints: [
                    {
                        id: 'validate',
                        method: 'POST',
                        path: '/coupons/validate',
                        shortPath: '/coupons/validate',
                        requiresAuth: true,
                        description: 'Valida un cupón y calcula el descuento. No lo consume ni registra redención.',
                        defaultBody: JSON.stringify({
                            code: "PROMO25",
                            amount: 50000,
                            phone: "3001234567"
                        }, null, 2),
                    },
                    {
                        id: 'redeem',
                        method: 'POST',
                        path: '/coupons/redeem',
                        shortPath: '/coupons/redeem',
                        requiresAuth: true,
                        description: 'Redime un cupón (lo marca como usado). Operación irreversible desde la API.',
                        defaultBody: JSON.stringify({
                            code: "PROMO25",
                            amount: 50000,
                            phone: "3001234567",
                            channel: "pos",
                            order_id: "ORD-0001"
                        }, null, 2),
                    },
                    {
                        id: 'coupon-show',
                        method: 'GET',
                        path: '/coupons/{code}',
                        shortPath: '/coupons/{code}',
                        requiresAuth: true,
                        description: 'Obtiene información de un cupón por su código.',
                        pathParams: [{ name: 'code', default: 'PROMO25' }],
                        defaultBody: '',
                    },
                ],
            },
            {
                label: 'Clientes',
                endpoints: [
                    {
                        id: 'customers-register',
                        method: 'POST',
                        path: '/customers/register',
                        shortPath: '/customers/register',
                        requiresAuth: true,
                        description: 'Registra un cliente nuevo o actualiza sus datos si ya existe.',
                        defaultBody: JSON.stringify({
                            document_type: "CC",
                            document_number: "12345678",
                            name: "Juan García",
                            phone: "3001234567",
                            email: "juan@ejemplo.com",
                            city_name: "Bogotá",
                            department: "Cundinamarca",
                            accept_terms: true,
                            accept_sms: true
                        }, null, 2),
                    },
                    {
                        id: 'customers-show',
                        method: 'GET',
                        path: '/customers/{document}',
                        shortPath: '/customers/{document}',
                        requiresAuth: true,
                        description: 'Consulta un cliente por número de documento o teléfono.',
                        pathParams: [{ name: 'document', default: '12345678' }],
                        defaultBody: '',
                    },
                    {
                        id: 'customers-terms',
                        method: 'POST',
                        path: '/customers/accept-terms',
                        shortPath: '/customers/accept-terms',
                        requiresAuth: true,
                        description: 'Registra la aceptación de un documento legal por parte del cliente.',
                        defaultBody: JSON.stringify({
                            document_number: "12345678",
                            document_type: "terms",
                            ip_address: "192.168.1.1"
                        }, null, 2),
                    },
                ],
            },
            {
                label: 'Legal',
                endpoints: [
                    {
                        id: 'legal-show',
                        method: 'GET',
                        path: '/legal/{type}',
                        shortPath: '/legal/{type}',
                        requiresAuth: false,
                        description: 'Retorna el documento legal vigente. Tipos: terms, privacy, sms_consent. No requiere autenticación.',
                        pathParams: [{ name: 'type', default: 'terms' }],
                        defaultBody: '',
                    },
                ],
            },
            {
                label: 'Notificaciones',
                endpoints: [
                    {
                        id: 'notify-send',
                        method: 'POST',
                        path: '/notify/send',
                        shortPath: '/notify/send',
                        requiresAuth: true,
                        description: 'Envía simultáneamente un SMS y un email. Retorna el estado de cada canal. Rate limit: 20/min.',
                        defaultBody: JSON.stringify({
                            phone: "3001234567",
                            email: "cliente@ejemplo.com",
                            sms_text: "Hola, tu descuento exclusivo está disponible. Más info en tu correo.",
                            email_subject: "Tu descuento exclusivo te espera 🎁",
                            email_template: "<html><body style=\"font-family:sans-serif;padding:24px\">\n  <h1 style=\"color:#1e40af\">¡Hola!</h1>\n  <p>Tu descuento exclusivo está listo.</p>\n  <p><strong>Código:</strong> PROMO25</p>\n  <a href=\"#\" style=\"background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none\">Usar mi descuento</a>\n</body></html>"
                        }, null, 2),
                    },
                ],
            },
            {
                label: 'Sistema',
                endpoints: [
                    {
                        id: 'health',
                        method: 'GET',
                        path: '/health',
                        shortPath: '/health',
                        requiresAuth: false,
                        description: 'Health check del servicio. Retorna 200 cuando está operativo.',
                        defaultBody: '',
                    },
                ],
            },
        ],

        init() {
            // Pre-select first endpoint
            this.selectEndpoint(this.endpointGroups[0].endpoints[0]);

            // Pre-fill credentials from localStorage if available
            const saved = localStorage.getItem('ch_tester_creds');
            if (saved) {
                try {
                    const { id, secret } = JSON.parse(saved);
                    this.clientId = id || '';
                    this.clientSecret = secret || '';
                } catch(e) {}
            }
        },

        selectEndpoint(ep) {
            this.selectedEndpoint = ep;
            this.body = ep.defaultBody || '';
            this.response = null;
            this.activeTab = ep.method !== 'GET' ? 'body' : (ep.pathParams?.length ? 'params' : 'headers');

            // init path params
            this.pathParams = {};
            if (ep.pathParams) {
                ep.pathParams.forEach(p => {
                    this.pathParams[p.name] = p.default || '';
                });
            }
        },

        hasPathParams() {
            return Object.keys(this.pathParams).length > 0;
        },

        buildUrl() {
            if (!this.selectedEndpoint) return '';
            let path = this.selectedEndpoint.path;
            // For /health, path is at the api root (not /v1)
            const base = this.selectedEndpoint.id === 'health'
                ? this.baseUrl.replace('/v1', '')
                : this.baseUrl;
            Object.entries(this.pathParams).forEach(([k, v]) => {
                path = path.replace(`{${k}}`, encodeURIComponent(v) || `{${k}}`);
            });
            return base + path;
        },

        async send() {
            if (!this.selectedEndpoint || this.loading) return;
            this.loading = true;
            this.response = null;

            // Save creds to localStorage
            localStorage.setItem('ch_tester_creds', JSON.stringify({
                id: this.clientId,
                secret: this.clientSecret,
            }));

            const ep = this.selectedEndpoint;
            const url = this.buildUrl();
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };

            if (ep.requiresAuth) {
                if (this.clientId)     headers['X-Client-Id']     = this.clientId;
                if (this.clientSecret) headers['X-Client-Secret'] = this.clientSecret;
            }

            const options = { method: ep.method, headers };

            if (ep.method !== 'GET' && this.body.trim()) {
                options.body = this.body;
            }

            const start = performance.now();
            try {
                const res = await fetch(url, options);
                const elapsed = Math.round(performance.now() - start);
                const text = await res.text();

                let formatted = text;
                try {
                    formatted = JSON.stringify(JSON.parse(text), null, 2);
                } catch(e) {}

                this.response = {
                    status: res.status,
                    statusText: res.statusText || this.httpStatusText(res.status),
                    time: elapsed,
                    body: formatted,
                    ok: res.ok,
                };
            } catch(err) {
                this.response = {
                    status: 0,
                    statusText: 'Error de red / CORS',
                    time: Math.round(performance.now() - start),
                    body: 'Error: ' + err.message + '\n\nSi ves un error de CORS, asegúrate de que APP_URL en .env coincide con la URL que estás usando.',
                    ok: false,
                };
            }

            this.loading = false;
        },

        formatBody() {
            try {
                this.body = JSON.stringify(JSON.parse(this.body), null, 2);
            } catch(e) {
                // invalid JSON, leave as-is
            }
        },

        async copyResponse() {
            if (!this.response) return;
            try {
                await navigator.clipboard.writeText(this.response.body);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch(e) {}
        },

        applyQuickSelect() {
            if (this.quickSelect) {
                this.clientId = this.quickSelect;
            }
        },

        methodBadge(method) {
            const map = {
                'GET':    'bg-green-100 text-green-700',
                'POST':   'bg-blue-100 text-blue-700',
                'PUT':    'bg-amber-100 text-amber-700',
                'DELETE': 'bg-red-100 text-red-700',
                'PATCH':  'bg-purple-100 text-purple-700',
            };
            return map[method] || 'bg-gray-100 text-gray-600';
        },

        statusBadge(status) {
            if (status >= 200 && status < 300) return 'bg-green-100 text-green-700';
            if (status >= 300 && status < 400) return 'bg-blue-100 text-blue-700';
            if (status >= 400 && status < 500) return 'bg-orange-100 text-orange-700';
            if (status >= 500) return 'bg-red-100 text-red-700';
            return 'bg-gray-100 text-gray-600';
        },

        httpStatusText(code) {
            const map = {
                200: 'OK', 201: 'Created', 204: 'No Content',
                400: 'Bad Request', 401: 'Unauthorized', 403: 'Forbidden',
                404: 'Not Found', 422: 'Unprocessable Entity', 429: 'Too Many Requests',
                500: 'Internal Server Error',
            };
            return map[code] || '';
        },

        // ── Export ──────────────────────────────────────────────────────────

        openExport() {
            this.exportModal = true;
            this.copiedExport = false;
        },

        _buildHeaders() {
            const ep = this.selectedEndpoint;
            const h = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };
            if (ep.requiresAuth) {
                if (this.clientId)     h['X-Client-Id']     = this.clientId;
                if (this.clientSecret) h['X-Client-Secret'] = this.clientSecret;
            }
            return h;
        },

        generateCurl() {
            if (!this.selectedEndpoint) return '';
            const ep     = this.selectedEndpoint;
            const url    = this.buildUrl();
            const hdrs   = this._buildHeaders();
            const method = ep.method;

            let lines = [`curl -X ${method} '${url}'`];

            Object.entries(hdrs).forEach(([k, v]) => {
                lines.push(`  -H '${k}: ${v}'`);
            });

            if (method !== 'GET' && this.body.trim()) {
                // Format body for readability
                let bodyStr = this.body.trim();
                try { bodyStr = JSON.stringify(JSON.parse(bodyStr), null, 2); } catch(e) {}
                // Escape single quotes
                bodyStr = bodyStr.replace(/'/g, "'\\''");
                lines.push(`  -d '${bodyStr}'`);
            }

            return lines.join(' \\\n');
        },

        generatePhp() {
            if (!this.selectedEndpoint) return '';
            const ep   = this.selectedEndpoint;
            const url  = this.buildUrl();
            const hdrs = this._buildHeaders();

            const hdrArray = Object.entries(hdrs)
                .map(([k, v]) => `    '${k}: ${v}',`)
                .join('\n');

            let bodySection = '';
            if (ep.method !== 'GET' && this.body.trim()) {
                let parsed;
                try {
                    parsed = JSON.parse(this.body);
                } catch(e) { parsed = null; }

                if (parsed !== null) {
                    bodySection = this._phpArray(parsed, 1);
                    bodySection = `\n    CURLOPT_POSTFIELDS => json_encode(${bodySection}),`;
                } else {
                    const escaped = this.body.trim().replace(/'/g, "\\'");
                    bodySection = `\n    CURLOPT_POSTFIELDS => '${escaped}',`;
                }
            }

            // '<?'+'php' — split to prevent Blade from parsing it as PHP
            const openTag = '<' + '?php';
            return openTag + `

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL            => '${url}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => '${ep.method}',
    CURLOPT_HTTPHEADER     => [
${hdrArray}
    ],${bodySection}
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    throw new RuntimeException('cURL error: ' . curl_error($ch));
}

$data = json_decode($response, true);

// Ejemplo de uso:
// if ($httpCode === 200 && $data['valid'] ?? false) { ... }`;
        },

        _phpArray(obj, depth) {
            const indent  = '    '.repeat(depth);
            const indent1 = '    '.repeat(depth + 1);

            if (Array.isArray(obj)) {
                if (obj.length === 0) return '[]';
                const items = obj.map(v => `${indent1}${this._phpValue(v, depth + 1)},`).join('\n');
                return `[\n${items}\n${indent}]`;
            }

            if (obj !== null && typeof obj === 'object') {
                const entries = Object.entries(obj);
                if (entries.length === 0) return '[]';
                const items = entries.map(([k, v]) =>
                    `${indent1}'${k}' => ${this._phpValue(v, depth + 1)},`
                ).join('\n');
                return `[\n${items}\n${indent}]`;
            }

            return this._phpValue(obj, depth);
        },

        _phpValue(v, depth) {
            if (v === null)             return 'null';
            if (v === true)             return 'true';
            if (v === false)            return 'false';
            if (typeof v === 'number')  return String(v);
            if (typeof v === 'string')  return `'${v.replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;
            if (typeof v === 'object')  return this._phpArray(v, depth);
            return `'${v}'`;
        },

        async copyExport() {
            const code = this.exportTab === 'curl' ? this.generateCurl() : this.generatePhp();
            try {
                await navigator.clipboard.writeText(code);
                this.copiedExport = true;
                setTimeout(() => this.copiedExport = false, 2500);
            } catch(e) {}
        },
    };
}
</script>
@endpush
@endsection
