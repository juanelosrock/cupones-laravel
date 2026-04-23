@extends('layouts.admin')
@section('title', 'Documentación API v1')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.api-clients.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">← API Clients</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-2xl font-bold text-gray-900">Documentación API v1</h1>
</div>

<div class="flex gap-6">

    {{-- Sidebar de navegación --}}
    <div class="w-52 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-6">
            <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-3">Contenido</p>
            <nav class="space-y-1 text-xs">
                <a href="#autenticacion" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">Autenticación</a>
                <a href="#rate-limiting" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">Rate limiting</a>
                <a href="#errores" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">Errores</a>
                <div class="border-t border-gray-100 my-2"></div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1 px-2">Cupones</p>
                <a href="#validate" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">POST /coupons/validate</a>
                <a href="#redeem" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">POST /coupons/redeem</a>
                <a href="#coupon-get" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">GET /coupons/{code}</a>
                <div class="border-t border-gray-100 my-2"></div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1 px-2">Clientes</p>
                <a href="#customers-register" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">POST /customers/register</a>
                <a href="#customers-get" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">GET /customers/{doc}</a>
                <a href="#customers-terms" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">POST /customers/accept-terms</a>
                <div class="border-t border-gray-100 my-2"></div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1 px-2">Legal</p>
                <a href="#legal" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">GET /legal/{type}</a>
                <div class="border-t border-gray-100 my-2"></div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-1 px-2">Notificaciones</p>
                <a href="#notify-send" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">POST /notify/send</a>
                <div class="border-t border-gray-100 my-2"></div>
                <a href="#health" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">GET /health</a>
                <div class="border-t border-gray-100 my-2"></div>
                <a href="#changelog" class="block py-1 px-2 rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">Changelog</a>
            </nav>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="flex-1 min-w-0 space-y-6">

        {{-- Intro --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-xl font-bold text-gray-900">CuponesHub REST API</h2>
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">v1 · Estable</span>
                    </div>
                    <p class="text-sm text-gray-600 max-w-2xl">
                        API REST sobre HTTPS para que sistemas POS, aplicaciones móviles e integraciones de terceros
                        validen y rediman cupones, gestionen clientes y consulten documentos legales vigentes.
                    </p>
                </div>
                <a href="{{ route('admin.api-clients.tester') }}"
                   class="flex-shrink-0 flex items-center gap-2 px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    API Tester
                </a>
            </div>
            <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Base URL</p>
                <code class="text-sm font-mono text-gray-800">{{ $baseUrl }}</code>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-3 text-xs">
                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="font-semibold text-blue-800 mb-1">Formato</p>
                    <p class="text-blue-700">JSON (UTF-8) en todas las respuestas. Enviar <code class="bg-blue-100 px-1 rounded">Content-Type: application/json</code> en requests con body.</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg border border-purple-100">
                    <p class="font-semibold text-purple-800 mb-1">Protocolo</p>
                    <p class="text-purple-700">HTTPS obligatorio en producción. En desarrollo puede usarse HTTP en localhost.</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                    <p class="font-semibold text-green-800 mb-1">Versionado</p>
                    <p class="text-green-700">Versión incluida en la URL (<code class="bg-green-100 px-1 rounded">/api/v1/</code>). Sin breaking changes entre minor releases.</p>
                </div>
            </div>
        </div>

        {{-- Autenticación --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="autenticacion">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Autenticación</h2>
            <p class="text-sm text-gray-500 mb-4">Todos los endpoints (excepto <code class="bg-gray-100 px-1 rounded text-xs">/health</code> y <code class="bg-gray-100 px-1 rounded text-xs">/legal</code>) requieren credenciales en cada request.</p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Headers requeridos</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Header</th>
                        <th class="px-4 py-2.5 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2"><code class="font-mono">X-Client-Id</code></td><td class="px-4 py-2 text-gray-600">Identificador público del cliente (ej: <code class="bg-gray-100 px-1 rounded">ch_abc123...</code>). No cambia al rotar el secret.</td></tr>
                        <tr><td class="px-4 py-2"><code class="font-mono">X-Client-Secret</code></td><td class="px-4 py-2 text-gray-600">Secret generado al crear o rotar las credenciales. Solo visible una vez. Verificado con bcrypt.</td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Ejemplo</h3>
            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>curl -X POST {{ $baseUrl }}/coupons/validate \
  -H "X-Client-Id: ch_TuClientId" \
  -H "X-Client-Secret: TuClientSecret" \
  -H "Content-Type: application/json" \
  -d '{"code": "PROMO25", "amount": 50000}'</pre>
            </div>

            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                <strong>Seguridad:</strong> Nunca expongas el <code>client_secret</code> en código frontend o apps móviles.
                Las credenciales deben usarse exclusivamente desde tu backend/servidor.
                Si sospechas que el secret fue comprometido, usa el botón "Rotar Secret" en el panel de administración.
            </div>
        </div>

        {{-- Rate Limiting --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="rate-limiting">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Rate limiting</h2>
            <p class="text-sm text-gray-500 mb-4">Cada cliente API tiene un límite de peticiones por minuto configurable. Adicionalmente, algunos endpoints tienen límites globales independientes del cliente.</p>

            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Endpoint</th>
                        <th class="px-4 py-2.5 text-left">Límite global</th>
                        <th class="px-4 py-2.5 text-left">Nota</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono">POST /coupons/validate</td><td class="px-4 py-2">30 req/min</td><td class="px-4 py-2 text-gray-500">Más el límite del cliente</td></tr>
                        <tr><td class="px-4 py-2 font-mono">POST /coupons/redeem</td><td class="px-4 py-2">10 req/min</td><td class="px-4 py-2 text-gray-500">Límite más estricto por ser operación de escritura</td></tr>
                        <tr><td class="px-4 py-2 font-mono">Demás endpoints</td><td class="px-4 py-2">60 req/min</td><td class="px-4 py-2 text-gray-500">—</td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Headers de respuesta en rate limit</h3>
            <p class="text-xs text-gray-500 mb-2">Cuando se supera el límite recibirás <code class="bg-gray-100 px-1 rounded">HTTP 429</code> con:</p>
            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{
  "error": "too_many_requests",
  "message": "Demasiadas peticiones. Intenta de nuevo en 60 segundos.",
  "retry_after": 60
}</pre>
            </div>
        </div>

        {{-- Errores --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="errores">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Manejo de errores</h2>
            <p class="text-sm text-gray-500 mb-4">Todos los errores retornan JSON con los campos <code class="bg-gray-100 px-1 rounded text-xs">error</code> (código máquina) y <code class="bg-gray-100 px-1 rounded text-xs">message</code> (descripción legible).</p>

            <div class="overflow-x-auto rounded-lg border border-gray-100">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">HTTP</th>
                        <th class="px-4 py-2.5 text-left">error</th>
                        <th class="px-4 py-2.5 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono text-red-600">401</td><td class="px-4 py-2 font-mono">unauthorized</td><td class="px-4 py-2 text-gray-600">Client-Id o Client-Secret inválidos</td></tr>
                        <tr><td class="px-4 py-2 font-mono text-red-600">401</td><td class="px-4 py-2 font-mono">client_inactive</td><td class="px-4 py-2 text-gray-600">El cliente API está desactivado o revocado</td></tr>
                        <tr><td class="px-4 py-2 font-mono text-red-600">401</td><td class="px-4 py-2 font-mono">client_expired</td><td class="px-4 py-2 text-gray-600">Las credenciales han vencido</td></tr>
                        <tr><td class="px-4 py-2 font-mono text-red-600">403</td><td class="px-4 py-2 font-mono">ip_not_allowed</td><td class="px-4 py-2 text-gray-600">La IP del cliente no está en la whitelist</td></tr>
                        <tr><td class="px-4 py-2 font-mono text-red-600">403</td><td class="px-4 py-2 font-mono">insufficient_scope</td><td class="px-4 py-2 text-gray-600">El cliente no tiene permiso para este endpoint</td></tr>
                        <tr><td class="px-4 py-2 font-mono text-orange-600">422</td><td class="px-4 py-2 font-mono">validation_error</td><td class="px-4 py-2 text-gray-600">Parámetros faltantes o inválidos. Incluye campo <code class="bg-gray-100 px-1 rounded">errors</code></td></tr>
                        <tr><td class="px-4 py-2 font-mono text-amber-600">429</td><td class="px-4 py-2 font-mono">too_many_requests</td><td class="px-4 py-2 text-gray-600">Se superó el rate limit. Ver campo <code class="bg-gray-100 px-1 rounded">retry_after</code></td></tr>
                        <tr><td class="px-4 py-2 font-mono text-gray-600">500</td><td class="px-4 py-2 font-mono">server_error</td><td class="px-4 py-2 text-gray-600">Error interno. Reportar con el campo <code class="bg-gray-100 px-1 rounded">request_id</code></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{{-- Ejemplo respuesta de error 422 --}}
{
  "error": "validation_error",
  "message": "Los datos proporcionados no son válidos.",
  "errors": {
    "code": ["El campo code es obligatorio."],
    "amount": ["El campo amount debe ser un número positivo."]
  }
}</pre>
            </div>
        </div>

        {{-- ── CUPONES ── --}}

        {{-- POST /coupons/validate --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="validate">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 font-mono font-bold rounded">POST</span>
                <code class="text-base font-mono text-gray-900">/coupons/validate</code>
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">scope: validate</span>
                <span class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full">30 req/min</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Valida un cupón y calcula el descuento aplicable. <strong>No consume el cupón</strong> ni registra una redención. Ideal para mostrar el descuento al cliente antes de confirmar la compra.</p>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Request body</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2 text-left">Campo</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Req.</th>
                        <th class="px-4 py-2 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono">code</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Código del cupón (case-insensitive)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">amount</td><td class="px-4 py-2 text-gray-500">number</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Monto total de la compra en COP (sin decimales)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">phone</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Teléfono del cliente (para verificar límite por usuario)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">document_number</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Documento del cliente (alternativa a phone)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">point_of_sale_id</td><td class="px-4 py-2 text-gray-500">integer</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">ID del PDV para validar restricciones geográficas</td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Respuesta exitosa (200)</h3>
            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto mb-4">
<pre>{
  "valid": true,
  "code": "PROMO25",
  "discount_type": "percentage",   // "percentage" | "fixed"
  "discount_value": 25.00,          // porcentaje o monto fijo
  "discount_amount": 12500.00,      // COP descontado
  "original_amount": 50000.00,
  "final_amount": 37500.00,
  "message": "Cupón válido. Se aplicará un descuento del 25%.",
  "coupon": {
    "starts_at": "2026-01-01",
    "expires_at": "2026-12-31",
    "min_purchase": 20000,
    "max_purchase": null,
    "uses_remaining": 48,           // null si ilimitado
    "applicable_to": "all"          // "all" | "products" | "categories"
  },
  "meta": {
    "request_id": "uuid-v4",
    "processed_at": "2026-04-12T10:00:00Z"
  }
}</pre>
            </div>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Cupón inválido (200)</h3>
            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{
  "valid": false,
  "code": "PROMO25",
  "message": "Este cupón ha alcanzado el límite de usos por usuario.",
  "meta": { "request_id": "uuid-v4", "processed_at": "..." }
}</pre>
            </div>
        </div>

        {{-- POST /coupons/redeem --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="redeem">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 font-mono font-bold rounded">POST</span>
                <code class="text-base font-mono text-gray-900">/coupons/redeem</code>
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">scope: redeem</span>
                <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full">10 req/min</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Redime un cupón. <strong>Operación de escritura irreversible desde la API</strong> — consume un uso del cupón y registra la redención. Usar solo al confirmar la compra.</p>

            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                <strong>Idempotencia:</strong> Si el sistema envía la misma petición dos veces por un timeout de red,
                se registrarán dos redenciones separadas. Implementa reintentos solo ante <code>500</code> — nunca ante <code>200</code> o <code>422</code>.
            </div>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Request body</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2 text-left">Campo</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Req.</th>
                        <th class="px-4 py-2 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono">code</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Código del cupón</td></tr>
                        <tr><td class="px-4 py-2 font-mono">amount</td><td class="px-4 py-2 text-gray-500">number</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Monto total de la compra en COP</td></tr>
                        <tr><td class="px-4 py-2 font-mono">phone</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Teléfono del cliente</td></tr>
                        <tr><td class="px-4 py-2 font-mono">document_number</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Documento del cliente</td></tr>
                        <tr><td class="px-4 py-2 font-mono">point_of_sale_id</td><td class="px-4 py-2 text-gray-500">integer</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">ID del punto de venta</td></tr>
                        <tr><td class="px-4 py-2 font-mono">channel</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600"><code class="bg-gray-100 px-1 rounded">pos</code>, <code class="bg-gray-100 px-1 rounded">app</code>, <code class="bg-gray-100 px-1 rounded">web</code>, <code class="bg-gray-100 px-1 rounded">api</code> (default: api)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">order_id</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">ID de la orden en tu sistema (para trazabilidad)</td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Respuesta exitosa (200)</h3>
            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{
  "redeemed": true,
  "redemption_id": 1234,
  "code": "PROMO25",
  "discount_type": "percentage",
  "discount_value": 25.00,
  "discount_amount": 12500.00,
  "original_amount": 50000.00,
  "final_amount": 37500.00,
  "message": "Cupón redimido exitosamente.",
  "meta": { "request_id": "uuid-v4", "processed_at": "..." }
}</pre>
            </div>
        </div>

        {{-- GET /coupons/{code} --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="coupon-get">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 font-mono font-bold rounded">GET</span>
                <code class="text-base font-mono text-gray-900">/coupons/{code}</code>
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">scope: validate</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Retorna la información pública de un cupón sin calcular el descuento. Útil para mostrar los detalles del cupón antes de iniciar una compra.</p>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Respuesta (200)</h3>
            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{
  "code": "PROMO25",
  "type": "unique",               // "unique" | "general"
  "discount_type": "percentage",
  "discount_value": 25.00,
  "min_purchase": 20000,
  "max_purchase": null,
  "starts_at": "2026-01-01",
  "expires_at": "2026-12-31",
  "status": "active",
  "uses_remaining": 48,
  "applicable_to": "all",
  "meta": { "request_id": "...", "processed_at": "..." }
}</pre>
            </div>
        </div>

        {{-- ── CLIENTES ── --}}

        {{-- POST /customers/register --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="customers-register">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 font-mono font-bold rounded">POST</span>
                <code class="text-base font-mono text-gray-900">/customers/register</code>
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">scope: customers</span>
            </div>
            <p class="text-sm text-gray-600 mb-3">Registra un nuevo cliente. Si ya existe un cliente con el mismo teléfono o documento, retorna el registro existente (upsert seguro).</p>
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                <strong>Ley 1581 (Colombia):</strong> Si el cliente acepta tratamiento de datos o consentimiento SMS,
                debes pasar <code>accept_privacy: true</code> y/o <code>accept_sms: true</code>.
                El sistema registrará la aceptación con el IP del request y timestamp.
            </div>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Request body</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2 text-left">Campo</th><th class="px-4 py-2 text-left">Tipo</th><th class="px-4 py-2 text-left">Req.</th><th class="px-4 py-2 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono">name</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Nombre</td></tr>
                        <tr><td class="px-4 py-2 font-mono">lastname</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Apellido</td></tr>
                        <tr><td class="px-4 py-2 font-mono">phone</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Teléfono celular (10 dígitos)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">document_type</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600"><code class="bg-gray-100 px-1 rounded">CC</code> <code class="bg-gray-100 px-1 rounded">CE</code> <code class="bg-gray-100 px-1 rounded">PA</code> <code class="bg-gray-100 px-1 rounded">TI</code> <code class="bg-gray-100 px-1 rounded">RC</code> <code class="bg-gray-100 px-1 rounded">NIT</code> <code class="bg-gray-100 px-1 rounded">DE</code></td></tr>
                        <tr><td class="px-4 py-2 font-mono">document_number</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Número de documento</td></tr>
                        <tr><td class="px-4 py-2 font-mono">email</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Correo electrónico</td></tr>
                        <tr class="bg-green-50"><td class="px-4 py-2 font-mono text-green-700">city_code</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Código DANE de la ciudad (ej: <code class="bg-gray-100 px-1 rounded">11001</code> = Bogotá)</td></tr>
                        <tr class="bg-green-50"><td class="px-4 py-2 font-mono text-green-700">city_name</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Nombre de la ciudad (alternativa a <code class="bg-gray-100 px-1 rounded">city_code</code>). Ej: <code class="bg-gray-100 px-1 rounded">Bogotá</code></td></tr>
                        <tr class="bg-green-50"><td class="px-4 py-2 font-mono text-green-700">department</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Nombre del departamento — mejora la resolución de ciudad cuando hay ambigüedad. Ej: <code class="bg-gray-100 px-1 rounded">Antioquia</code></td></tr>
                        <tr><td class="px-4 py-2 font-mono">accept_privacy</td><td class="px-4 py-2 text-gray-500">boolean</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Acepta política de privacidad (Ley 1581) — debe ser <code class="bg-gray-100 px-1 rounded">true</code></td></tr>
                        <tr><td class="px-4 py-2 font-mono">accept_terms</td><td class="px-4 py-2 text-gray-500">boolean</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Acepta términos y condiciones — debe ser <code class="bg-gray-100 px-1 rounded">true</code></td></tr>
                        <tr><td class="px-4 py-2 font-mono">accept_sms</td><td class="px-4 py-2 text-gray-500">boolean</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Consentimiento de envío de SMS — registra la aceptación del documento <code class="bg-gray-100 px-1 rounded">sms_consent</code></td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Respuesta (201 created / 200 existing)</h3>
            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{
  "customer": {
    "id": 42,
    "name": "Ana",
    "lastname": "García",
    "full_name": "Ana García",
    "phone": "3001234567",
    "email": "ana@email.com",
    "document_type": "CC",
    "document_number": "1023456789",
    "city": "Medellín",
    "department": "Antioquia",
    "status": "active",
    "data_treatment_accepted": true,
    "data_treatment_accepted_at": "2026-04-12T10:00:00Z"
  },
  "created": true,   // false si ya existía
  "message": "Cliente registrado correctamente.",
  "meta": { "request_id": "...", "processed_at": "..." }
}</pre>
            </div>
        </div>

        {{-- GET /customers/{document} --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="customers-get">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 font-mono font-bold rounded">GET</span>
                <code class="text-base font-mono text-gray-900">/customers/{identifier}</code>
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">scope: customers</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Consulta un cliente por número de documento o teléfono. El <code class="bg-gray-100 px-1 rounded text-xs">identifier</code> puede ser el documento o el teléfono celular (10 dígitos).</p>

            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto mb-4">
<pre>GET {{ $baseUrl }}/customers/3001234567
GET {{ $baseUrl }}/customers/1023456789</pre>
            </div>

            <p class="text-xs text-gray-500">Retorna <code class="bg-gray-100 px-1 rounded">404</code> con <code class="bg-gray-100 px-1 rounded">error: "not_found"</code> si no existe el cliente.</p>
        </div>

        {{-- POST /customers/accept-terms --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="customers-terms">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 font-mono font-bold rounded">POST</span>
                <code class="text-base font-mono text-gray-900">/customers/accept-terms</code>
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">scope: customers</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Registra la aceptación de uno o más documentos legales por parte de un cliente existente. Cumplimiento Ley 1581.</p>

            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Request body</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2 text-left">Campo</th><th class="px-4 py-2 text-left">Tipo</th><th class="px-4 py-2 text-left">Req.</th><th class="px-4 py-2 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono">phone</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Teléfono del cliente (identifica al cliente)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">document_types</td><td class="px-4 py-2 text-gray-500">array</td><td class="px-4 py-2"><span class="text-red-500">✓</span></td><td class="px-4 py-2 text-gray-600">Tipos a aceptar: <code class="bg-gray-100 px-1 rounded">privacy</code>, <code class="bg-gray-100 px-1 rounded">terms</code>, <code class="bg-gray-100 px-1 rounded">sms_consent</code></td></tr>
                        <tr><td class="px-4 py-2 font-mono">channel</td><td class="px-4 py-2 text-gray-500">string</td><td class="px-4 py-2 text-gray-400">—</td><td class="px-4 py-2 text-gray-600">Canal de aceptación: <code class="bg-gray-100 px-1 rounded">pos</code>, <code class="bg-gray-100 px-1 rounded">app</code>, <code class="bg-gray-100 px-1 rounded">web</code> (default: api)</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>// Request
{ "phone": "3001234567", "document_types": ["privacy", "sms_consent"], "channel": "pos" }

// Response 200
{ "accepted": ["privacy", "sms_consent"], "meta": { "request_id": "...", "processed_at": "..." } }</pre>
            </div>
        </div>

        {{-- ── LEGAL ── --}}

        <div class="bg-white rounded-xl shadow-sm p-6" id="legal">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 font-mono font-bold rounded">GET</span>
                <code class="text-base font-mono text-gray-900">/legal/{type}</code>
                <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-medium">Público — sin auth</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Retorna el documento legal vigente del tipo especificado. No requiere autenticación.</p>

            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2 text-left">type</th><th class="px-4 py-2 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono">terms</td><td class="px-4 py-2 text-gray-600">Términos y condiciones</td></tr>
                        <tr><td class="px-4 py-2 font-mono">privacy</td><td class="px-4 py-2 text-gray-600">Política de privacidad y tratamiento de datos (Ley 1581)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">sms_consent</td><td class="px-4 py-2 text-gray-600">Consentimiento para envío de SMS</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{
  "type": "privacy",
  "version": "1.0",
  "title": "Política de Privacidad",
  "content": "...",
  "published_at": "2026-01-01T00:00:00Z",
  "meta": { "request_id": "...", "processed_at": "..." }
}</pre>
            </div>
        </div>

        {{-- Notify --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="notify-send">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 font-mono font-bold rounded">POST</span>
                <code class="text-base font-mono text-gray-900">/notify/send</code>
                <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-medium">20 req/min</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Envía simultáneamente un <strong>SMS</strong> y un <strong>email HTML</strong> a un destinatario. Ambos envíos se realizan de forma síncrona y se registran en el log de notificaciones. Retorna siempre <code class="bg-gray-100 px-1 rounded text-xs">200</code> — verificar <code class="bg-gray-100 px-1 rounded text-xs">sms.status</code> y <code class="bg-gray-100 px-1 rounded text-xs">email.status</code> para confirmar el resultado de cada canal.</p>

            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2 text-left">Campo</th><th class="px-4 py-2 text-left">Tipo</th><th class="px-4 py-2 text-left">Requerido</th><th class="px-4 py-2 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr><td class="px-4 py-2 font-mono">phone</td><td class="px-4 py-2">string</td><td class="px-4 py-2 text-green-700 font-medium">Sí</td><td class="px-4 py-2">Número celular del destinatario (ej: 3001234567)</td></tr>
                        <tr><td class="px-4 py-2 font-mono">email</td><td class="px-4 py-2">string</td><td class="px-4 py-2 text-green-700 font-medium">Sí</td><td class="px-4 py-2">Correo electrónico válido del destinatario</td></tr>
                        <tr><td class="px-4 py-2 font-mono">sms_text</td><td class="px-4 py-2">string</td><td class="px-4 py-2 text-green-700 font-medium">Sí</td><td class="px-4 py-2">Texto del SMS — máximo 160 caracteres</td></tr>
                        <tr><td class="px-4 py-2 font-mono">email_subject</td><td class="px-4 py-2">string</td><td class="px-4 py-2 text-green-700 font-medium">Sí</td><td class="px-4 py-2">Asunto del correo — máximo 255 caracteres</td></tr>
                        <tr><td class="px-4 py-2 font-mono">email_template</td><td class="px-4 py-2">string</td><td class="px-4 py-2 text-green-700 font-medium">Sí</td><td class="px-4 py-2">Cuerpo del email en formato HTML5</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto mb-4">
<pre>// Request
POST /api/v1/notify/send
Content-Type: application/json
X-Client-Id: ch_demo_client
X-Client-Secret: ...

{
  "phone": "3001234567",
  "email": "cliente@ejemplo.com",
  "sms_text": "Hola, tu descuento exclusivo te espera. Revisa tu correo.",
  "email_subject": "Tu descuento exclusivo 🎁",
  "email_template": "&lt;html&gt;&lt;body&gt;&lt;h1&gt;Hola!&lt;/h1&gt;&lt;p&gt;Tu código: PROMO25&lt;/p&gt;&lt;/body&gt;&lt;/html&gt;"
}</pre>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>// Respuesta exitosa
{
  "success": true,
  "sms": {
    "status": "sent",
    "message_id": "abc123xyz",
    "error": null
  },
  "email": {
    "status": "sent",
    "message_id": "def456uvw",
    "error": null
  },
  "meta": {
    "request_id": "uuid",
    "processed_at": "2026-04-23T10:00:00Z"
  }
}

// Respuesta con fallo parcial (el SMS falló, el email se envió)
{
  "success": false,
  "sms": { "status": "failed", "message_id": null, "error": "Zenvia error 400: ..." },
  "email": { "status": "sent", "message_id": "def456uvw", "error": null },
  "meta": { ... }
}</pre>
            </div>
        </div>

        {{-- Health --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="health">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 font-mono font-bold rounded">GET</span>
                <code class="text-base font-mono text-gray-900">/health</code>
                <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-medium">Público — sin auth</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Health check del servicio. Útil para monitoreo y load balancers. Retorna <code class="bg-gray-100 px-1 rounded text-xs">200</code> cuando el servicio está operativo.</p>

            <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>{
  "status": "ok",
  "version": "1.0",
  "timestamp": "2026-04-12T10:00:00Z"
}</pre>
            </div>
        </div>

        {{-- Ejemplos de código --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Ejemplos de integración</h2>

            <div x-data="{ tab: 'curl' }" class="space-y-3">
                <div class="flex gap-2">
                    <button @click="tab='curl'" :class="tab==='curl' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="text-xs px-3 py-1.5 rounded-lg font-mono transition-colors">cURL</button>
                    <button @click="tab='php'" :class="tab==='php' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="text-xs px-3 py-1.5 rounded-lg font-mono transition-colors">PHP</button>
                    <button @click="tab='node'" :class="tab==='node' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="text-xs px-3 py-1.5 rounded-lg font-mono transition-colors">Node.js</button>
                </div>

                <div x-show="tab==='curl'">
                    <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre># Validar cupón
curl -X POST {{ $baseUrl }}/coupons/validate \
  -H "X-Client-Id: TU_CLIENT_ID" \
  -H "X-Client-Secret: TU_CLIENT_SECRET" \
  -H "Content-Type: application/json" \
  -d '{"code":"PROMO25","amount":50000,"phone":"3001234567"}'

# Redimir cupón
curl -X POST {{ $baseUrl }}/coupons/redeem \
  -H "X-Client-Id: TU_CLIENT_ID" \
  -H "X-Client-Secret: TU_CLIENT_SECRET" \
  -H "Content-Type: application/json" \
  -d '{"code":"PROMO25","amount":50000,"phone":"3001234567","channel":"pos","order_id":"ORD-9821"}'</pre>
                    </div>
                </div>

                <div x-show="tab==='php'">
                    <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>&lt;?php
$client = new \GuzzleHttp\Client([
    'base_uri' => '{{ $baseUrl }}',
    'headers'  => [
        'X-Client-Id'     => 'TU_CLIENT_ID',
        'X-Client-Secret' => 'TU_CLIENT_SECRET',
        'Content-Type'    => 'application/json',
    ],
]);

// Validar cupón
$response = $client->post('/coupons/validate', [
    'json' => [
        'code'   => 'PROMO25',
        'amount' => 50000,
        'phone'  => '3001234567',
    ],
]);
$data = json_decode($response->getBody(), true);

if ($data['valid']) {
    echo "Descuento: $" . number_format($data['discount_amount']);
}

// Redimir cupón
$response = $client->post('/coupons/redeem', [
    'json' => [
        'code'     => 'PROMO25',
        'amount'   => 50000,
        'phone'    => '3001234567',
        'channel'  => 'pos',
        'order_id' => 'ORD-9821',
    ],
]);</pre>
                    </div>
                </div>

                <div x-show="tab==='node'">
                    <div class="bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-100 overflow-x-auto">
<pre>const axios = require('axios');

const api = axios.create({
  baseURL: '{{ $baseUrl }}',
  headers: {
    'X-Client-Id':     'TU_CLIENT_ID',
    'X-Client-Secret': 'TU_CLIENT_SECRET',
    'Content-Type':    'application/json',
  },
});

// Validar cupón
const { data } = await api.post('/coupons/validate', {
  code:   'PROMO25',
  amount: 50000,
  phone:  '3001234567',
});

if (data.valid) {
  console.log(`Descuento: $${data.discount_amount.toLocaleString('es-CO')}`);
}

// Manejo de errores
try {
  await api.post('/coupons/redeem', { code: 'PROMO25', amount: 50000 });
} catch (err) {
  if (err.response?.status === 429) {
    const retryAfter = err.response.data.retry_after;
    // esperar y reintentar
  }
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Changelog --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="changelog">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Changelog</h2>
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-shrink-0 text-xs text-gray-400 w-24 pt-0.5">2026-04-12</div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">v1.0 — Lanzamiento inicial</p>
                        <ul class="mt-1 text-xs text-gray-600 space-y-0.5 list-disc list-inside">
                            <li>Endpoints de validación y redención de cupones</li>
                            <li>Registro y consulta de clientes</li>
                            <li>Aceptación de documentos legales (Ley 1581)</li>
                            <li>Consulta de documentos legales vigentes</li>
                            <li>Autenticación por X-Client-Id / X-Client-Secret</li>
                            <li>Rate limiting por cliente y por endpoint</li>
                            <li>Log de todas las peticiones con anti-replay (SHA256)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
