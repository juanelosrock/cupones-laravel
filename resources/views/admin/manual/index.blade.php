@extends('layouts.admin')
@section('title', 'Manual de Usuario')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manual de Usuario</h1>
        <p class="text-sm text-gray-500 mt-0.5">Guía completa para el uso del panel de administración CuponesHub</p>
    </div>
    <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full font-medium">v1.2 — Abril 2026</span>
</div>

<div class="flex gap-6">

    {{-- Sidebar de navegación --}}
    <div class="w-56 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-6" x-data>
            <p class="text-[10px] uppercase tracking-wider text-gray-400 mb-3 font-semibold">Contenido</p>
            <nav class="space-y-0.5 text-xs">
                <a href="#introduccion"    class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Introducción</a>
                <a href="#acceso"          class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Acceso al sistema</a>
                <a href="#dashboard"       class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Dashboard</a>
                <p class="pt-2 pb-1 px-2 text-[10px] uppercase tracking-wider text-gray-400 font-semibold">Cupones</p>
                <a href="#campanas"        class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Campañas</a>
                <a href="#lotes"           class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Lotes de Cupones</a>
                <a href="#redenciones"     class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Redenciones</a>
                <p class="pt-2 pb-1 px-2 text-[10px] uppercase tracking-wider text-gray-400 font-semibold">Clientes</p>
                <a href="#clientes"        class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Clientes</a>
                <a href="#sms"             class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Campañas SMS</a>
                <a href="#landing-pages"  class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Landing Pages</a>
                <p class="pt-2 pb-1 px-2 text-[10px] uppercase tracking-wider text-gray-400 font-semibold">Configuración</p>
                <a href="#usuarios"        class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Usuarios</a>
                <a href="#roles"           class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Roles y Permisos</a>
                <a href="#geografia"       class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Geografía</a>
                <a href="#legales"         class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Documentos Legales</a>
                <a href="#api-clients"     class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">API Clients</a>
                <a href="#api-tester"     class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">API Tester</a>
                <a href="#auditoria"       class="block py-1.5 px-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">Auditoría</a>
            </nav>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="flex-1 min-w-0 space-y-6">

        {{-- ── INTRODUCCIÓN ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="introduccion">
            <h2 class="text-xl font-bold text-gray-900 mb-3">Introducción</h2>
            <p class="text-sm text-gray-600 mb-4">
                <strong>CuponesHub</strong> es una plataforma de gestión de cupones de descuento diseñada para que tu equipo pueda
                crear, distribuir y controlar promociones de forma centralizada. Permite además gestionar la base de clientes,
                enviar SMS masivos y conectar sistemas externos como puntos de venta (POS) a través de una API REST.
            </p>
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs font-semibold text-blue-800 mb-1">Flujo principal</p>
                    <p class="text-xs text-blue-700">Campaña → Lote de cupones → Generación de códigos → Distribución → Redención</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                    <p class="text-xs font-semibold text-green-800 mb-1">Clientes</p>
                    <p class="text-xs text-green-700">Registro con aceptación de datos (Ley 1581), segmentación por ciudad y zona para SMS</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg border border-purple-100">
                    <p class="text-xs font-semibold text-purple-800 mb-1">Integraciones</p>
                    <p class="text-xs text-purple-700">API REST para que POS y apps validen y rediman cupones en tiempo real</p>
                </div>
            </div>
        </div>

        {{-- ── ACCESO ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="acceso">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Acceso al sistema</h2>
            <p class="text-sm text-gray-500 mb-4">Cómo ingresar y cerrar sesión</p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Ingresar</h3>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside mb-4">
                <li>Abre el navegador e ingresa la dirección del sistema (la URL que te proporcionó el administrador).</li>
                <li>Ingresa tu correo electrónico y contraseña.</li>
                <li>Haz clic en <strong>Iniciar sesión</strong>.</li>
            </ol>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-4">
                <strong>Seguridad:</strong> El sistema bloquea el acceso después de 5 intentos fallidos consecutivos en 10 minutos
                y genera una alerta de seguridad. Si no puedes ingresar, contacta al administrador para restablecer tu contraseña.
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cerrar sesión</h3>
            <p class="text-sm text-gray-600">
                Haz clic en tu nombre en la esquina superior derecha y luego en <strong>Salir</strong>.
                Siempre cierra sesión al terminar, especialmente en equipos compartidos.
            </p>
        </div>

        {{-- ── DASHBOARD ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="dashboard">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Dashboard</h2>
            <p class="text-sm text-gray-500 mb-4">Vista general del estado del sistema</p>

            <p class="text-sm text-gray-600 mb-3">
                El dashboard es la primera pantalla que ves al ingresar. Muestra un resumen en tiempo real de las métricas
                más importantes del sistema.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Indicadores que muestra</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Indicador</th>
                        <th class="px-4 py-2.5 text-left">Qué significa</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-medium">Redenciones hoy</td><td class="px-4 py-2 text-gray-600">Cuántos cupones se han canjeado en el día actual</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Cupones activos</td><td class="px-4 py-2 text-gray-600">Total de códigos disponibles para ser usados</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Clientes registrados</td><td class="px-4 py-2 text-gray-600">Base total de clientes en el sistema</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Descuento entregado hoy</td><td class="px-4 py-2 text-gray-600">Suma de los descuentos aplicados en el día (en COP)</td></tr>
                    </tbody>
                </table>
            </div>

            <p class="text-sm text-gray-600">
                También verás un listado de las <strong>redenciones más recientes</strong> y los <strong>lotes con más actividad</strong>.
                Usa el dashboard para detectar rápidamente picos de uso o anomalías.
            </p>
        </div>

        {{-- ── CAMPAÑAS ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="campanas">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Campañas</h2>
            <p class="text-sm text-gray-500 mb-4">Agrupador principal de lotes de cupones</p>

            <p class="text-sm text-gray-600 mb-4">
                Una <strong>campaña</strong> es el contenedor general de una promoción. Por ejemplo:
                "Descuentos de Temporada Navideña 2026". Dentro de cada campaña puedes tener uno o varios
                <strong>lotes de cupones</strong> con reglas específicas.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Tipos de campaña</h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs font-semibold text-gray-800">Descuento</p>
                    <p class="text-xs text-gray-500 mt-0.5">Promociones estándar por porcentaje o monto fijo</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs font-semibold text-gray-800">Lealtad</p>
                    <p class="text-xs text-gray-500 mt-0.5">Recompensas para clientes frecuentes</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs font-semibold text-gray-800">Referido</p>
                    <p class="text-xs text-gray-500 mt-0.5">Cupones generados por recomendaciones de clientes</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs font-semibold text-gray-800">Promoción</p>
                    <p class="text-xs text-gray-500 mt-0.5">Ofertas especiales de tiempo limitado</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 col-span-2">
                    <p class="text-xs font-semibold text-amber-800">Autorización de Datos <span class="ml-1 text-[10px] bg-amber-200 text-amber-700 px-1.5 py-0.5 rounded-full">Especial</span></p>
                    <p class="text-xs text-amber-700 mt-0.5">Campaña para obtener consentimiento de clientes que aún no han autorizado el tratamiento de sus datos personales (Ley 1581). El sistema <strong>excluye automáticamente</strong> a los clientes que ya tienen autorización registrada. Solo aplica para segmentación — no genera cupones de descuento.</p>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear una campaña</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>Campañas</strong> en el menú lateral.</li>
                <li>Haz clic en <strong>Nueva Campaña</strong>.</li>
                <li>Ingresa un nombre descriptivo (ej: "Promo Día de la Madre").</li>
                <li>Selecciona el tipo de campaña.</li>
                <li>Define las fechas de inicio y fin de la campaña.</li>
                <li>Guarda. Luego puedes agregarle lotes de cupones y asignar clientes desde el detalle.</li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Asignar clientes a una campaña</h3>
            <p class="text-sm text-gray-600 mb-2">
                Desde el detalle de una campaña, el botón <strong>Asignar clientes</strong> abre una página con dos métodos:
            </p>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 mb-1">1. Importar CSV</p>
                    <p class="text-gray-500">Sube un archivo con clientes nuevos. Se crean en el sistema y quedan vinculados a la campaña automáticamente. Columnas: <code class="bg-gray-100 px-1 rounded">celular</code> (requerido), <code class="bg-gray-100 px-1 rounded">nombre</code>, <code class="bg-gray-100 px-1 rounded">email</code>, <code class="bg-gray-100 px-1 rounded">departamento</code>, <code class="bg-gray-100 px-1 rounded">ciudad</code>.</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 mb-1">2. Segmentación por ubicación</p>
                    <p class="text-gray-500">Selecciona departamentos y/o ciudades de la base de clientes existente. Usa <strong>Previsualizar</strong> para ver el conteo antes de confirmar la asignación.</p>
                </div>
            </div>
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-4">
                <strong>Regla de autorización automática:</strong> Para campañas de tipo <em>Autorización de Datos</em>, solo se asignan clientes <strong>sin</strong> autorización registrada. Para cualquier otro tipo de campaña, solo se asignan clientes <strong>con</strong> autorización. Esta regla se aplica en ambos métodos (CSV e importación por filtro).
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Estados de una campaña</h3>
            <div class="flex gap-3 flex-wrap text-xs mb-4">
                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full">Borrador — creada pero no activa</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full">Activa — aceptando redenciones</span>
                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full">Pausada — detenida temporalmente</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full">Finalizada — fuera de fechas</span>
            </div>

            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-800">
                <strong>Tip:</strong> Usa la función <strong>Duplicar campaña</strong> para crear rápidamente una promoción
                similar a una existente, sin tener que configurar todo desde cero.
            </div>
        </div>

        {{-- ── LOTES DE CUPONES ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="lotes">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Lotes de Cupones</h2>
            <p class="text-sm text-gray-500 mb-4">Define las reglas y genera los códigos de descuento</p>

            <p class="text-sm text-gray-600 mb-4">
                Un <strong>lote</strong> es un conjunto de cupones con las mismas reglas (descuento, fechas, límites).
                Cada lote pertenece a una campaña.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Tipos de lote</h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-sm font-semibold text-blue-800 mb-1">Código único</p>
                    <p class="text-xs text-blue-700">
                        Genera N códigos individuales, cada uno de un solo uso. Ideal para enviar por SMS o email
                        de forma personalizada. Ejemplo: <code class="bg-blue-100 px-1 rounded">PROMO-A3K9FX2Q</code>
                    </p>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-100">
                    <p class="text-sm font-semibold text-purple-800 mb-1">Código general</p>
                    <p class="text-xs text-purple-700">
                        Un solo código compartido que múltiples clientes pueden usar hasta un límite total.
                        Ideal para publicar en redes sociales. Ejemplo: <code class="bg-purple-100 px-1 rounded">VERANO2026</code>
                    </p>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Tipos de descuento</h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800">Porcentaje (%)</p>
                    <p class="text-gray-500 mt-0.5">Ejemplo: 25% sobre el total de la compra. Si compras $80.000, el descuento es $20.000.</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800">Monto fijo ($)</p>
                    <p class="text-gray-500 mt-0.5">Ejemplo: $15.000 de descuento fijo. Si el descuento supera el total, solo se descuenta hasta el valor de la compra.</p>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Reglas de uso que puedes configurar</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Regla</th>
                        <th class="px-4 py-2.5 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-medium">Compra mínima</td><td class="px-4 py-2 text-gray-600">El cliente debe comprar al menos este monto para que aplique el cupón</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Compra máxima</td><td class="px-4 py-2 text-gray-600">El cupón no aplica si la compra supera este monto (opcional)</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Usos totales</td><td class="px-4 py-2 text-gray-600">Cuántas veces en total se puede usar el lote entre todos los clientes</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Usos por cliente</td><td class="px-4 py-2 text-gray-600">Cuántas veces puede usar el mismo cupón un mismo cliente</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Usos por día</td><td class="px-4 py-2 text-gray-600">Límite diario de redenciones en todo el lote</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Fechas de validez</td><td class="px-4 py-2 text-gray-600">El cupón solo funciona entre la fecha de inicio y la de fin</td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear un lote paso a paso</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>Lotes de Cupones</strong> → <strong>Nuevo Lote</strong>.</li>
                <li>Selecciona la campaña a la que pertenece.</li>
                <li>Define el nombre del lote (interno, no lo ve el cliente).</li>
                <li>Elige el tipo: <em>Único</em> o <em>General</em>.</li>
                <li>Si es único, indica cuántos códigos generar y el prefijo (ej: <code class="bg-gray-100 px-1 rounded">PROMO</code>).</li>
                <li>Si es general, escribe el código exacto (ej: <code class="bg-gray-100 px-1 rounded">VERANO2026</code>).</li>
                <li>Configura el descuento, fechas y límites de uso.</li>
                <li>Guarda. Para lotes únicos, la <strong>generación de códigos se hace en segundo plano</strong> — puede tardar unos minutos si son muchos códigos.</li>
            </ol>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-3">
                <strong>Importante:</strong> El lote debe estar en estado <strong>Activo</strong> para que los cupones
                puedan ser redimidos. Si lo ves en estado "Borrador" o "Pausado", usa el botón <strong>Activar</strong>.
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Exportar cupones</h3>
            <p class="text-sm text-gray-600">
                Desde el detalle de un lote puedes descargar todos los códigos en formato CSV
                para distribuirlos por email, SMS u otros canales.
            </p>
        </div>

        {{-- ── REDENCIONES ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="redenciones">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Redenciones</h2>
            <p class="text-sm text-gray-500 mb-4">Registro completo de cada cupón canjeado</p>

            <p class="text-sm text-gray-600 mb-4">
                Cada vez que un cliente usa un cupón, se registra una <strong>redención</strong> con todos los detalles:
                quién lo usó, cuándo, en qué canal, qué descuento se aplicó y cuánto pagó finalmente.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Información que registra cada redención</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Campo</th>
                        <th class="px-4 py-2.5 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-medium">Código</td><td class="px-4 py-2 text-gray-600">El cupón que se canjeó</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Cliente</td><td class="px-4 py-2 text-gray-600">Nombre y teléfono del cliente</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Canal</td><td class="px-4 py-2 text-gray-600">Dónde se usó: POS, App, Web o API</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Monto original</td><td class="px-4 py-2 text-gray-600">Valor de la compra antes del descuento</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Descuento aplicado</td><td class="px-4 py-2 text-gray-600">Cuánto se descontó</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Monto final</td><td class="px-4 py-2 text-gray-600">Lo que pagó el cliente</td></tr>
                        <tr><td class="px-4 py-2 font-medium">Fecha y hora</td><td class="px-4 py-2 text-gray-600">Cuándo se realizó la redención</td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Filtros disponibles</h3>
            <p class="text-sm text-gray-600 mb-3">
                Puedes filtrar el listado por: fecha, campaña, lote, canal o estado (activa / revertida).
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Revertir una redención</h3>
            <p class="text-sm text-gray-600 mb-2">
                Si un cliente devuelve la compra o hubo un error en la caja, puedes revertir la redención:
            </p>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside mb-3">
                <li>Abre el detalle de la redención haciendo clic en ella.</li>
                <li>Haz clic en <strong>Revertir redención</strong>.</li>
                <li>El cupón queda disponible nuevamente y el uso se descuenta del contador.</li>
            </ol>
            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800">
                <strong>Importante:</strong> La reversión queda registrada en la auditoría con el usuario
                que la realizó y la fecha. No se puede revertir una redención que ya fue revertida previamente.
            </div>
        </div>

        {{-- ── CLIENTES ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="clientes">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Clientes</h2>
            <p class="text-sm text-gray-500 mb-4">Gestión de la base de clientes con cumplimiento Ley 1581</p>

            <p class="text-sm text-gray-600 mb-4">
                El módulo de clientes almacena la información de las personas que participan en las promociones.
                Por cumplimiento de la <strong>Ley 1581 de Colombia</strong> (protección de datos personales),
                todo registro debe tener la aceptación explícita del cliente para el tratamiento de sus datos.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Formas de registrar clientes</h3>
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 mb-1">Manual</p>
                    <p class="text-gray-500">Agente llena el formulario uno por uno. Útil para registro en punto de venta.</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 mb-1">Importación CSV</p>
                    <p class="text-gray-500">Carga masiva desde Excel. Ideal para migrar bases de datos existentes.</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 mb-1">API</p>
                    <p class="text-gray-500">Sistemas externos como apps o POS registran clientes automáticamente.</p>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear un cliente manualmente</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>Clientes</strong> → <strong>Nuevo Cliente</strong>.</li>
                <li>Ingresa los datos básicos: nombre, celular (obligatorio), documento.</li>
                <li>Selecciona el departamento y luego la ciudad.</li>
                <li>Marca la casilla de <strong>Aceptación de tratamiento de datos</strong> — es obligatorio.</li>
                <li>Si el cliente también acepta recibir SMS, marca esa casilla adicional.</li>
                <li>Guarda. El sistema registra la IP, fecha y canal de la aceptación automáticamente.</li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Importar desde CSV</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-3">
                <li>Ve a <strong>Clientes → Importar CSV</strong> y descarga la <strong>Plantilla CSV</strong>.</li>
                <li>Abre el archivo en Excel y completa los datos (no cambies los nombres de las columnas).</li>
                <li>La columna <code class="bg-gray-100 px-1 rounded text-xs">acepta_datos</code> debe ser <strong>si</strong> para cumplir Ley 1581.</li>
                <li>Guarda como CSV. El sistema acepta separador <code class="bg-gray-100 px-1 rounded text-xs">;</code> o <code class="bg-gray-100 px-1 rounded text-xs">,</code> — lo detecta automáticamente.</li>
                <li>Sube el archivo. El sistema muestra: nuevos, duplicados omitidos y errores por fila.</li>
            </ol>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-xs border border-gray-200 rounded-lg">
                    <thead><tr class="bg-gray-50 text-gray-500"><th class="px-3 py-2 text-left border-b">Columna</th><th class="px-3 py-2 text-left border-b">Req.</th><th class="px-3 py-2 text-left border-b">También acepta</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="px-3 py-1.5 font-mono">celular</td><td class="px-3 py-1.5 text-red-600 font-semibold">Sí</td><td class="px-3 py-1.5 font-mono text-gray-400">telefono, movil</td></tr>
                        <tr><td class="px-3 py-1.5 font-mono">nombre</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">nombres</td></tr>
                        <tr><td class="px-3 py-1.5 font-mono">apellido</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">apellidos</td></tr>
                        <tr><td class="px-3 py-1.5 font-mono">correo</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">email, mail</td></tr>
                        <tr class="bg-green-50"><td class="px-3 py-1.5 font-mono text-green-700">departamento</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">depto</td></tr>
                        <tr class="bg-green-50"><td class="px-3 py-1.5 font-mono text-green-700">ciudad</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">codigo_ciudad (DANE)</td></tr>
                        <tr><td class="px-3 py-1.5 font-mono">numero_documento</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">documento, cedula</td></tr>
                        <tr class="bg-blue-50"><td class="px-3 py-1.5 font-mono text-blue-700">acepta_datos</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">autoriza_datos</td></tr>
                        <tr class="bg-blue-50"><td class="px-3 py-1.5 font-mono text-blue-700">acepta_sms</td><td class="px-3 py-1.5 text-gray-400">No</td><td class="px-3 py-1.5 font-mono text-gray-400">sms</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-xs text-green-800 mb-4">
                <strong>Mismo formato para campañas:</strong> Las columnas <code class="bg-green-100 px-1 rounded">departamento</code> y <code class="bg-green-100 px-1 rounded">ciudad</code> son idénticas a las que usa la asignación a campañas. Puedes usar el mismo archivo CSV tanto para importar clientes al sistema como para asignarlos a una campaña.
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Bloquear / Desbloquear un cliente</h3>
            <p class="text-sm text-gray-600 mb-3">
                Un cliente bloqueado no puede redimir cupones. Desde el perfil del cliente usa los botones
                <strong>Bloquear</strong> o <strong>Desbloquear</strong>. El bloqueo queda registrado en auditoría.
            </p>

            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-800">
                <strong>Dato importante:</strong> El sistema nunca elimina clientes definitivamente (soft delete).
                Si un cliente solicita la eliminación de sus datos bajo Ley 1581, contacta al administrador
                del sistema para el proceso de baja formal.
            </div>
        </div>

        {{-- ── CAMPAÑAS SMS ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="sms">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Campañas SMS</h2>
            <p class="text-sm text-gray-500 mb-4">Envío masivo de mensajes de texto con cupones</p>

            <p class="text-sm text-gray-600 mb-4">
                Las campañas SMS permiten enviar mensajes de texto masivos a clientes segmentados,
                incluyendo su código de cupón personalizado. Solo se envía a clientes que hayan
                aceptado recibir comunicaciones SMS.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear una campaña SMS</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>Campañas SMS</strong> → <strong>Nueva Campaña</strong>.</li>
                <li>Escribe el mensaje. Puedes usar variables: <code class="bg-gray-100 px-1 rounded text-xs">{nombre}</code>, <code class="bg-gray-100 px-1 rounded text-xs">{codigo}</code>, <code class="bg-gray-100 px-1 rounded text-xs">{descuento}</code>.</li>
                <li>Selecciona el lote de cupones del que se tomarán los códigos.</li>
                <li>Filtra el público: por ciudad, zona, punto de venta o segmento.</li>
                <li>Revisa el número estimado de destinatarios.</li>
                <li>Elige <strong>Enviar ahora</strong> o programa para una fecha futura.</li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Variables en el mensaje</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Variable</th>
                        <th class="px-4 py-2.5 text-left">Se reemplaza por</th>
                        <th class="px-4 py-2.5 text-left">Ejemplo</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-mono">{nombre}</td><td class="px-4 py-2 text-gray-600">Nombre del cliente</td><td class="px-4 py-2 text-gray-400">Ana García</td></tr>
                        <tr><td class="px-4 py-2 font-mono">{codigo}</td><td class="px-4 py-2 text-gray-600">Código del cupón asignado</td><td class="px-4 py-2 text-gray-400">PROMO-A3K9</td></tr>
                        <tr><td class="px-4 py-2 font-mono">{descuento}</td><td class="px-4 py-2 text-gray-600">Valor del descuento</td><td class="px-4 py-2 text-gray-400">25% o $15.000</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-3">
                <strong>Ejemplo de mensaje:</strong> "Hola {nombre}, tienes un descuento del {descuento} esperándote. Usa el código {codigo} en tu próxima compra. Válido hasta el 30 de abril."
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Estados del envío</h3>
            <div class="flex gap-2 flex-wrap text-xs mb-3">
                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full">Borrador</span>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full">Programada</span>
                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full">En envío</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full">Completada</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full">Cancelada</span>
            </div>

            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-800">
                <strong>Procesamiento en segundo plano:</strong> El envío masivo se procesa en cola.
                Para que funcione, debe estar corriendo el comando <code class="bg-blue-100 px-1 rounded">php artisan queue:work</code>
                en el servidor. Consulta al administrador técnico si los envíos no progresan.
            </div>
        </div>

        {{-- ── LANDING PAGES ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="landing-pages">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Landing Pages</h2>
            <p class="text-sm text-gray-500 mb-4">Páginas de autorización de datos personalizadas para tus campañas SMS</p>

            <p class="text-sm text-gray-600 mb-4">
                Cuando activas el <strong>enlace de autorización</strong> en una campaña SMS, el cliente recibe un enlace único que lo lleva a
                una página donde acepta el uso de sus datos. Las landing pages te permiten personalizar el diseño de esa página
                con el branding de tu empresa.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-3">4 plantillas disponibles</h3>
            <div class="grid grid-cols-2 gap-3 mb-5">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs font-semibold text-gray-800 mb-1">Minimal</p>
                    <p class="text-xs text-gray-500">Fondo claro con tarjeta blanca centrada. Limpio y profesional.</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs font-semibold text-blue-800 mb-1">Branded</p>
                    <p class="text-xs text-blue-700">Encabezado con tu color de marca y logo. Ideal para reconocimiento de marca.</p>
                </div>
                <div class="p-3 bg-gray-800 rounded-lg border border-gray-700">
                    <p class="text-xs font-semibold text-white mb-1">Hero</p>
                    <p class="text-xs text-gray-400">Imagen de fondo a pantalla completa con formulario flotante semitransparente.</p>
                </div>
                <div class="p-3 rounded-lg border" style="background:#fff7ed;border-color:#fed7aa;">
                    <p class="text-xs font-semibold mb-1" style="color:#c2410c;">Promo ⭐ Nuevo</p>
                    <p class="text-xs" style="color:#9a3412;">Muestra el descuento del cupón en grande (ej: 50% OFF) con formulario de registro de cliente. Capta correo y teléfono, crea o actualiza el cliente automáticamente y registra la aceptación de todos los documentos legales.</p>
                </div>
            </div>

            <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg mb-4">
                <p class="text-xs font-semibold text-orange-800 mb-2">Template Promo — comportamiento especial</p>
                <ul class="text-xs text-orange-700 space-y-1.5 list-disc list-inside">
                    <li><strong>El título (heading)</strong> se muestra como el gran badge negro debajo del porcentaje (ej: "DOMICILIOS").</li>
                    <li><strong>El subtítulo</strong> se usa como encabezado del formulario de registro (ej: "Regístrate").</li>
                    <li>El cliente ingresa su <strong>teléfono</strong> (obligatorio) y <strong>correo</strong> (opcional).</li>
                    <li>Si el teléfono ya existe, se actualiza el email. Si no existe, se crea el cliente automáticamente.</li>
                    <li>Un solo checkbox acepta simultáneamente: tratamiento de datos, T&C, política de privacidad y consentimiento SMS.</li>
                    <li>Las aceptaciones de los tres documentos legales activos quedan registradas en el historial del cliente.</li>
                </ul>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Qué se puede personalizar</h3>
            <div class="grid grid-cols-2 gap-2 mb-4">
                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                    <li>Logo de la empresa (subida o URL)</li>
                    <li>Color de marca (botones, acentos)</li>
                    <li>Color de fondo</li>
                    <li>Imagen de fondo (solo template Hero)</li>
                </ul>
                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                    <li>Título y subtítulo</li>
                    <li>Cuerpo del mensaje (editor de texto enriquecido)</li>
                    <li>Texto del botón principal</li>
                    <li>Mensajes de confirmación y pie de página</li>
                </ul>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear una landing page</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>Landing Pages</strong> → <strong>Nueva landing</strong>.</li>
                <li>Dale un nombre interno descriptivo (solo visible en el panel).</li>
                <li>Elige la plantilla: Minimal, Branded, Hero o <strong>Promo</strong>.</li>
                <li>Configura los colores y sube el logo (o pega una URL de imagen).</li>
                <li>Edita los textos con el editor enriquecido.</li>
                <li>Guarda y usa el botón <strong>Previsualizar</strong> para ver cómo queda.</li>
                <li>Al crear una campaña SMS con autorización, selecciona tu landing en el paso 4.</li>
            </ol>

            <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-xs text-green-800">
                <strong>Landing por defecto:</strong> Puedes marcar una landing como "por defecto".
                Se aplicará automáticamente a todas las campañas SMS que no tengan una landing específica asignada.
                Si no hay ninguna por defecto, se usa el diseño estándar del sistema.
            </div>
        </div>

        {{-- ── USUARIOS ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="usuarios">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Usuarios</h2>
            <p class="text-sm text-gray-500 mb-4">Gestión de quién puede acceder al panel de administración</p>

            <p class="text-sm text-gray-600 mb-4">
                Los <strong>usuarios</strong> son las personas del equipo que tienen acceso al panel admin.
                No confundas con los <em>clientes</em>, que son los consumidores finales de los cupones.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Roles disponibles</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Rol</th>
                        <th class="px-4 py-2.5 text-left">Acceso</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-medium">super-admin</td><td class="px-4 py-2 text-gray-600">Acceso total. Puede crear usuarios, roles y configurar todo el sistema.</td></tr>
                        <tr><td class="px-4 py-2 font-medium">admin</td><td class="px-4 py-2 text-gray-600">Gestión completa de campañas, clientes y cupones. No puede modificar usuarios ni roles.</td></tr>
                        <tr><td class="px-4 py-2 font-medium">operador</td><td class="px-4 py-2 text-gray-600">Puede ver redenciones y clientes, pero no crear o modificar campañas.</td></tr>
                        <tr><td class="px-4 py-2 font-medium">analista</td><td class="px-4 py-2 text-gray-600">Solo lectura. Puede ver reportes y estadísticas sin hacer cambios.</td></tr>
                    </tbody>
                </table>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear un usuario</h3>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside mb-3">
                <li>Ve a <strong>Usuarios</strong> → <strong>Nuevo Usuario</strong>.</li>
                <li>Ingresa nombre, correo y contraseña temporal.</li>
                <li>Asigna el rol correspondiente.</li>
                <li>Guarda. El usuario podrá ingresar de inmediato con esas credenciales.</li>
            </ol>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                <strong>Buenas prácticas:</strong> Asigna el rol mínimo necesario para la función del usuario.
                Usa <em>operador</em> para personal de caja o servicio al cliente; <em>analista</em> para
                quien solo necesita ver reportes.
            </div>
        </div>

        {{-- ── ROLES ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="roles">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Roles y Permisos</h2>
            <p class="text-sm text-gray-500 mb-4">Control detallado de accesos por módulo</p>

            <p class="text-sm text-gray-600 mb-4">
                Si los roles predeterminados no se ajustan a tu organización, puedes crear roles personalizados
                con exactamente los permisos que necesitas.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear un rol personalizado</h3>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside mb-3">
                <li>Ve a <strong>Roles</strong> → <strong>Nuevo Rol</strong>.</li>
                <li>Escribe el nombre del rol (ej: "supervisor-ventas").</li>
                <li>Marca los permisos que tendrá (ver, crear, editar, eliminar por módulo).</li>
                <li>Guarda. Luego asigna este rol a los usuarios desde el módulo de Usuarios.</li>
            </ol>

            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-800">
                <strong>Nota:</strong> El rol <em>super-admin</em> tiene todos los permisos por defecto y
                no puede ser modificado. Es el rol del administrador principal del sistema.
            </div>
        </div>

        {{-- ── GEOGRAFÍA ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="geografia">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Geografía</h2>
            <p class="text-sm text-gray-500 mb-4">Administración de ciudades, zonas y puntos de venta</p>

            <p class="text-sm text-gray-600 mb-4">
                El módulo de geografía permite organizar los <strong>puntos de venta (PDV)</strong> por zona y ciudad.
                Esta segmentación se usa para filtrar clientes en campañas SMS y para las restricciones de cupones por ubicación.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Estructura jerárquica</h3>
            <div class="flex items-center gap-2 text-xs mb-4 flex-wrap">
                <span class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-lg font-medium">País (Colombia)</span>
                <span class="text-gray-400">→</span>
                <span class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-lg font-medium">Departamento</span>
                <span class="text-gray-400">→</span>
                <span class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-lg font-medium">Ciudad / Municipio</span>
                <span class="text-gray-400">→</span>
                <span class="px-3 py-1.5 bg-purple-100 text-purple-800 rounded-lg font-medium">Zona</span>
                <span class="text-gray-400">→</span>
                <span class="px-3 py-1.5 bg-green-100 text-green-800 rounded-lg font-medium">Punto de Venta</span>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo agregar un punto de venta</h3>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside mb-3">
                <li>Ve a <strong>Geografía</strong>.</li>
                <li>Selecciona la pestaña <strong>Puntos de Venta</strong>.</li>
                <li>Haz clic en <strong>Nuevo PDV</strong>.</li>
                <li>Ingresa nombre, ciudad y zona (si aplica).</li>
                <li>Guarda. El PDV aparecerá disponible al crear restricciones de cupones y filtros SMS.</li>
            </ol>

            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-800">
                <strong>Dato:</strong> El sistema viene precargado con todos los municipios de Colombia
                (códigos DANE) y 9 zonas de Bogotá. No necesitas agregar ciudades manualmente.
            </div>
        </div>

        {{-- ── DOCUMENTOS LEGALES ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="legales">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Documentos Legales</h2>
            <p class="text-sm text-gray-500 mb-4">Gestión de T&C, política de privacidad y consentimiento SMS</p>

            <p class="text-sm text-gray-600 mb-4">
                Para cumplir con la <strong>Ley 1581 de Colombia</strong>, el sistema registra la aceptación
                de los documentos legales por cada cliente. Este módulo permite crear y publicar nuevas versiones
                de estos documentos cuando cambian.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Tipos de documentos</h3>
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800">Términos y Condiciones</p>
                    <p class="text-gray-500 mt-0.5">Reglas de uso del servicio y los cupones</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800">Política de Privacidad</p>
                    <p class="text-gray-500 mt-0.5">Cómo se usan y protegen los datos personales</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800">Consentimiento SMS</p>
                    <p class="text-gray-500 mt-0.5">Autorización para enviar mensajes de texto</p>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo publicar una nueva versión</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>Documentos Legales</strong> → <strong>Nuevo Documento</strong>.</li>
                <li>Selecciona el tipo (términos, privacidad o consentimiento SMS).</li>
                <li>Escribe el número de versión (ej: "2.0") y el título.</li>
                <li>Pega el contenido del documento.</li>
                <li>Guarda como borrador y revísalo.</li>
                <li>Cuando esté listo, haz clic en <strong>Publicar</strong>. Esta versión pasa a ser la vigente.</li>
            </ol>

            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800">
                <strong>Atención:</strong> Al publicar una nueva versión, la anterior queda inactiva automáticamente.
                Los clientes que ya aceptaron la versión anterior <em>no</em> son notificados automáticamente;
                si necesitas nueva aceptación, coordina con tu equipo legal.
            </div>
        </div>

        {{-- ── API CLIENTS ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="api-clients">
            <h2 class="text-xl font-bold text-gray-900 mb-1">API Clients</h2>
            <p class="text-sm text-gray-500 mb-4">Credenciales de acceso para sistemas externos</p>

            <p class="text-sm text-gray-600 mb-4">
                Cuando un sistema externo (como un POS, una app o un sitio web) necesita validar o redimir
                cupones automáticamente, debe autenticarse con un <strong>cliente API</strong>.
                Cada cliente tiene un identificador único (<code class="bg-gray-100 px-1 rounded text-xs">Client ID</code>)
                y una contraseña secreta (<code class="bg-gray-100 px-1 rounded text-xs">Client Secret</code>).
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo crear un nuevo cliente API</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>API Clients</strong> → <strong>Nuevo Cliente API</strong>.</li>
                <li>Escribe un nombre descriptivo (ej: "POS Sede Norte — Producción").</li>
                <li>Selecciona el entorno: <strong>Producción</strong> o <strong>Sandbox</strong> (pruebas).</li>
                <li>Marca los permisos que necesita (validate, redeem, customers, etc.).</li>
                <li>Define el límite de peticiones por minuto.</li>
                <li>Opcionalmente, restringe el acceso a IPs específicas.</li>
                <li>Haz clic en <strong>Generar Credenciales</strong>.</li>
                <li>
                    <strong class="text-red-600">Copia el Client Secret inmediatamente</strong> — solo se muestra una vez.
                    Si lo pierdes, deberás rotar el secret para generar uno nuevo.
                </li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Acciones sobre un cliente API</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-4 py-2.5 text-left">Acción</th>
                        <th class="px-4 py-2.5 text-left">Cuándo usarla</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-4 py-2 font-medium text-orange-600">Rotar Secret</td><td class="px-4 py-2 text-gray-600">El secret fue comprometido, perdido o como práctica de seguridad periódica. El Client ID no cambia.</td></tr>
                        <tr><td class="px-4 py-2 font-medium text-amber-600">Desactivar</td><td class="px-4 py-2 text-gray-600">Detener temporalmente el acceso (mantenimiento, revisión). Se puede reactivar.</td></tr>
                        <tr><td class="px-4 py-2 font-medium text-green-600">Reactivar</td><td class="px-4 py-2 text-gray-600">Restablecer un cliente desactivado.</td></tr>
                        <tr><td class="px-4 py-2 font-medium text-red-600">Revocar</td><td class="px-4 py-2 text-gray-600">Cancelación permanente e irreversible. Las credenciales quedan inútiles para siempre.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-4">
                <strong>Consejo de seguridad:</strong> Crea un cliente API diferente para cada sistema o ambiente.
                Nunca compartas credenciales entre producción y sandbox, ni entre sistemas diferentes.
                Así, si uno se compromete, solo necesitas revocar ese cliente sin afectar los demás.
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Campos disponibles al registrar un cliente vía API</h3>
            <p class="text-sm text-gray-600 mb-3">
                El endpoint <code class="bg-gray-100 px-1 rounded text-xs">POST /api/v1/customers/register</code> acepta los siguientes campos.
                La ciudad se puede indicar de dos formas: con código DANE o con nombre de ciudad + departamento (igual que en la importación CSV).
            </p>
            <div class="overflow-x-auto rounded-lg border border-gray-100 mb-4">
                <table class="w-full text-xs">
                    <thead><tr class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500 border-b">
                        <th class="px-3 py-2 text-left">Campo</th>
                        <th class="px-3 py-2 text-left">Req.</th>
                        <th class="px-3 py-2 text-left">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr><td class="px-3 py-2 font-mono">name</td><td class="px-3 py-2 text-red-600 font-semibold">Sí</td><td class="px-3 py-2 text-gray-600">Nombre del cliente</td></tr>
                        <tr><td class="px-3 py-2 font-mono">phone</td><td class="px-3 py-2 text-red-600 font-semibold">Sí</td><td class="px-3 py-2 text-gray-600">Teléfono — identifica al cliente (upsert por teléfono)</td></tr>
                        <tr><td class="px-3 py-2 font-mono">accept_privacy</td><td class="px-3 py-2 text-red-600 font-semibold">Sí</td><td class="px-3 py-2 text-gray-600">Debe ser <code class="bg-gray-100 px-1 rounded">true</code> — acepta política de privacidad (Ley 1581)</td></tr>
                        <tr><td class="px-3 py-2 font-mono">accept_terms</td><td class="px-3 py-2 text-red-600 font-semibold">Sí</td><td class="px-3 py-2 text-gray-600">Debe ser <code class="bg-gray-100 px-1 rounded">true</code> — acepta términos y condiciones</td></tr>
                        <tr><td class="px-3 py-2 font-mono">document_type</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2 text-gray-600">CC, CE, PA, TI, RC, NIT, DE</td></tr>
                        <tr><td class="px-3 py-2 font-mono">document_number</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2 text-gray-600">Número de documento</td></tr>
                        <tr class="bg-green-50"><td class="px-3 py-2 font-mono text-green-700">city_code</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2 text-gray-600">Código DANE de la ciudad. Ej: <code class="bg-gray-100 px-1 rounded">11001</code></td></tr>
                        <tr class="bg-green-50"><td class="px-3 py-2 font-mono text-green-700">city_name</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2 text-gray-600">Nombre de la ciudad. Ej: <code class="bg-gray-100 px-1 rounded">Medellín</code></td></tr>
                        <tr class="bg-green-50"><td class="px-3 py-2 font-mono text-green-700">department</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2 text-gray-600">Nombre del departamento — desambigua cuando hay ciudades con el mismo nombre</td></tr>
                        <tr><td class="px-3 py-2 font-mono">accept_sms</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2 text-gray-600">Consentimiento de envío de SMS — registra la aceptación del documento SMS</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mb-4">
                La respuesta incluye los campos <code class="bg-gray-100 px-1 rounded">city</code> y <code class="bg-gray-100 px-1 rounded">department</code> con el nombre resuelto.
                Ver documentación completa en <strong>API Clients → Ver Documentación</strong>.
            </p>
        </div>

        {{-- ── API TESTER ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="api-tester">
            <h2 class="text-xl font-bold text-gray-900 mb-1">API Tester</h2>
            <p class="text-sm text-gray-500 mb-4">Herramienta integrada para probar los endpoints del API sin salir del panel</p>

            <p class="text-sm text-gray-600 mb-4">
                El <strong>API Tester</strong> es una herramienta tipo Postman integrada directamente en el panel.
                Permite enviar peticiones reales a todos los endpoints del API, ver la respuesta formateada y
                verificar que las credenciales de un cliente funcionen correctamente.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo acceder</h3>
            <p class="text-sm text-gray-600 mb-4">
                Desde <strong>API Clients</strong> haz clic en el botón <strong>"API Tester"</strong> en la esquina superior derecha,
                o desde la <strong>Documentación API</strong> donde también aparece el mismo botón.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo usarlo</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>En el panel izquierdo, selecciona el cliente API del dropdown (se pre-llena el Client ID) o escríbelo manualmente junto con el <code class="bg-gray-100 px-1 rounded text-xs">Client Secret</code>.</li>
                <li>Haz clic en el endpoint que quieres probar (agrupados por Cupones, Clientes, Legal, Sistema).</li>
                <li>Modifica el <strong>Body (JSON)</strong> con los datos de prueba. Usa el botón <strong>Formatear</strong> para darle formato automático.</li>
                <li>Si el endpoint tiene parámetro en la ruta (ej: <code class="bg-gray-100 px-1 rounded text-xs">{code}</code>), edítalo en la pestaña <strong>Path params</strong>.</li>
                <li>Revisa los headers que se enviarán en la pestaña <strong>Headers</strong>.</li>
                <li>Haz clic en <strong>Enviar</strong> — verás la respuesta con código HTTP, tiempo de respuesta y el JSON completo.</li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Exportar como cURL o PHP</h3>
            <p class="text-sm text-gray-600 mb-3">
                El botón <strong>Exportar</strong> (junto al botón Enviar) genera el código completo de la petición
                lista para copiar y usar fuera del panel:
            </p>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 mb-1">cURL</p>
                    <p class="text-gray-500">Comando listo para pegar en terminal. Compatible con Postman e Insomnia (importar cURL).</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 mb-1">PHP (cURL)</p>
                    <p class="text-gray-500">Código PHP completo con <code class="bg-gray-100 px-1 rounded">curl_setopt_array</code>, <code class="bg-gray-100 px-1 rounded">json_encode</code> y manejo de errores listo para integrar.</p>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Notas importantes</h3>
            <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside mb-4">
                <li>Las peticiones se envían <strong>directamente desde tu navegador</strong> — no pasan por el servidor del panel.</li>
                <li>Las credenciales se guardan en el <em>localStorage</em> del navegador para no tener que reingresarlas.</li>
                <li>El endpoint <strong>POST /coupons/redeem</strong> <span class="text-red-600 font-medium">consume el cupón</span> — úsalo con cuidado en producción.</li>
                <li>Los endpoints de <strong>Legal</strong> y <strong>Health</strong> son públicos y no requieren credenciales.</li>
                <li>Los canales válidos para redimir son: <code class="bg-gray-100 px-1 rounded">api</code>, <code class="bg-gray-100 px-1 rounded">web</code>, <code class="bg-gray-100 px-1 rounded">pos</code>, <code class="bg-gray-100 px-1 rounded">app</code>, <code class="bg-gray-100 px-1 rounded">manual</code>, <code class="bg-gray-100 px-1 rounded">sms</code>.</li>
            </ul>

            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                <strong>Tip:</strong> Usa el API Tester con las credenciales del cliente "Demo" para hacer pruebas
                sin afectar datos de producción. El secret del cliente demo lo encuentras en
                <strong>API Clients → ch_demo_client → ver detalle</strong>.
            </div>
        </div>

        {{-- ── AUDITORÍA ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6" id="auditoria">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Auditoría</h2>
            <p class="text-sm text-gray-500 mb-4">Registro de todas las acciones realizadas en el sistema</p>

            <p class="text-sm text-gray-600 mb-4">
                El módulo de auditoría registra automáticamente <strong>cada acción importante</strong> que ocurre
                en el sistema: quién creó o modificó un cupón, quién bloqueó un cliente, quién cambió un rol, etc.
                No requiere ninguna acción por parte del usuario — todo se registra solo.
            </p>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Qué queda registrado</h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                    <li>Creación y modificación de campañas y lotes</li>
                    <li>Activación y pausa de lotes de cupones</li>
                    <li>Reversión de redenciones</li>
                    <li>Bloqueo / desbloqueo de clientes</li>
                    <li>Importación y asignación de clientes</li>
                    <li>Creación de campañas SMS y envíos</li>
                </ul>
                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                    <li>Creación y cambios en usuarios</li>
                    <li>Rotación y revocación de API clients</li>
                    <li>Publicación de documentos legales</li>
                    <li>Creación y edición de landing pages</li>
                    <li>Alertas de seguridad (intentos fallidos, IPs bloqueadas)</li>
                </ul>
            </div>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Cómo usar el log de auditoría</h3>
            <ol class="text-sm text-gray-600 space-y-1.5 list-decimal list-inside mb-4">
                <li>Ve a <strong>Auditoría</strong> en el menú lateral.</li>
                <li>Revisa las <strong>alertas de seguridad</strong> en la parte superior si las hay.</li>
                <li>Usa los filtros: <em>Acción</em>, <em>Usuario</em>, <em>Módulo</em>, <em>Rango de fechas</em> o <em>IP</em>.</li>
                <li>Haz clic en <strong>Ver →</strong> en cualquier fila para ver el detalle completo del cambio.</li>
                <li>En el detalle verás los <strong>valores anteriores y nuevos</strong> de cada campo modificado.</li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-800 mb-2">Alertas de seguridad</h3>
            <p class="text-sm text-gray-600 mb-3">
                Las alertas aparecen cuando el sistema detecta actividad anómala, como múltiples intentos de login
                fallidos o accesos desde IPs no autorizadas. Márcalas como <strong>Resueltas</strong> una vez investigadas.
            </p>

            <div class="p-3 bg-green-50 border border-green-100 rounded-lg text-xs text-green-800">
                <strong>Trazabilidad completa:</strong> Cada registro incluye usuario, fecha y hora exactas, IP de origen
                y los valores campo por campo antes y después del cambio.
                Los registros no se pueden eliminar — garantizan trazabilidad para auditorías externas o incidentes legales.
            </div>
        </div>

        {{-- ── PREGUNTAS FRECUENTES ── --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Preguntas frecuentes</h2>

            <div class="space-y-4" x-data="{ open: null }">

                @php
                $faqs = [
                    ['¿Por qué el cupón dice "no encontrado" si lo acabo de crear?',
                     'Es posible que el lote esté en estado Borrador o Pausado. Ve a Lotes de Cupones, abre el lote y haz clic en Activar. Si el lote es de códigos únicos, también verifica que el job de generación haya terminado (puede tardar unos minutos para lotes grandes).'],
                    ['¿Se puede deshacer la revocación de un cliente API?',
                     'No. La revocación es permanente e irreversible. Si necesitas restablecer el acceso, debes crear un nuevo cliente API y configurar el sistema externo con las nuevas credenciales.'],
                    ['¿Qué pasa si el cliente usa el mismo cupón dos veces?',
                     'El sistema lo detecta automáticamente según las reglas del lote. Si el lote tiene límite de 1 uso por cliente, el segundo intento devolverá un mensaje de "límite de usos alcanzado".'],
                    ['¿Cómo sé si una campaña SMS se está enviando?',
                     'Ve a Campañas SMS y abre la campaña. En el detalle verás el progreso de envíos: cuántos se enviaron exitosamente, cuántos fallaron y cuántos están pendientes. El envío se procesa en segundo plano.'],
                    ['¿Puedo cambiar el descuento de un lote que ya está activo?',
                     'Sí, puedes editar un lote activo. Sin embargo, los cambios aplican solo a nuevas redenciones; las ya realizadas no se modifican. Si el cambio es significativo, considera crear un nuevo lote en lugar de modificar el existente.'],
                    ['¿Cómo exporto el listado de redenciones para análisis?',
                     'En el módulo de Redenciones, filtra según necesites y usa el botón Exportar CSV que aparece en la esquina superior del listado. El archivo se descarga con todos los registros del filtro activo.'],
                    ['Un cliente dice que no puede redimir su cupón aunque es válido. ¿Qué reviso?',
                     'Verifica: (1) Que el lote esté en estado Activo. (2) Que la fecha actual esté dentro del rango de validez. (3) Que el cliente no esté bloqueado. (4) Que no haya alcanzado el límite de usos por usuario. (5) Que el monto de la compra cumpla con el mínimo requerido.'],
                    ['Subí una imagen de fondo en la landing page pero no se ve en el preview. ¿Qué hago?',
                     'La imagen de fondo solo aplica al template Hero. Verifica que en la configuración de tu landing page el campo "Plantilla" esté seleccionado como Hero (el tercero, con fondo oscuro). Si usas Minimal, Branded o Promo, la imagen de fondo no se muestra. Edita la landing, cambia la plantilla a Hero y guarda.'],
                    ['El logo de la landing page aparece roto (URL de imagen no encontrada). ¿Cómo lo soluciono?',
                     'Si subiste el logo desde tu computadora, el archivo se guarda en el servidor. Asegúrate de que el servidor tenga el enlace de almacenamiento activo (php artisan storage:link). Si cambiaste de servidor, deberás subir la imagen nuevamente desde el panel de edición de la landing page.'],
                ];
                @endphp

                @foreach($faqs as $i => [$pregunta, $respuesta])
                <div class="border border-gray-100 rounded-lg overflow-hidden">
                    <button @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                            class="w-full flex items-center justify-between px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors">
                        <span class="text-sm font-medium text-gray-800">{{ $pregunta }}</span>
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 ml-4 transition-transform"
                             :class="open === {{ $i }} ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse class="px-4 py-3 text-sm text-gray-600 bg-white border-t border-gray-100">
                        {{ $respuesta }}
                    </div>
                </div>
                @endforeach

            </div>
        </div>

        {{-- Footer del manual --}}
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-xs text-gray-400">Manual de Usuario — CuponesHub v1.1 · Abril 2026</p>
            <p class="text-xs text-gray-400 mt-1">Para soporte técnico o consultas, contacta al administrador del sistema.</p>
        </div>

    </div>
</div>
@endsection
