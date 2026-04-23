<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — CuponesHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-blue-900 text-white flex flex-col transform transition-transform duration-200"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="flex items-center justify-between h-16 px-6 bg-blue-950 flex-shrink-0">
        <span class="text-xl font-bold tracking-tight">🎟 CuponesHub</span>
        <button @click="sidebarOpen=false" class="lg:hidden text-blue-300 hover:text-white">✕</button>
    </div>
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800' : '' }}">
            📊 Dashboard
        </a>
        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase">Cupones</div>
        <a href="{{ route('admin.campaigns.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.campaigns.*') ? 'bg-blue-800' : '' }}">
            📣 Campañas
        </a>
        <a href="{{ route('admin.coupon-batches.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.coupon-batches.*') ? 'bg-blue-800' : '' }}">
            🎫 Lotes de Cupones
        </a>
        <a href="{{ route('admin.redemptions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.redemptions.*') ? 'bg-blue-800' : '' }}">
            ✅ Redenciones
        </a>
        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase">Clientes</div>
        <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.customers.*') ? 'bg-blue-800' : '' }}">
            👥 Clientes
        </a>
        <a href="{{ route('admin.sms-campaigns.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.sms-campaigns.*') ? 'bg-blue-800' : '' }}">
            📱 Campañas SMS
        </a>
        <a href="{{ route('admin.email-campaigns.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.email-campaigns.*') ? 'bg-blue-800' : '' }}">
            📧 Campañas Email
        </a>
        <a href="{{ route('admin.landing-configs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.landing-configs.*') ? 'bg-blue-800' : '' }}">
            🖼️ Landing Pages
        </a>
        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase">Configuración</div>
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.users.*') ? 'bg-blue-800' : '' }}">
            👤 Usuarios
        </a>
        <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.roles.*') ? 'bg-blue-800' : '' }}">
            🔐 Roles
        </a>
        <a href="{{ route('admin.geography.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.geography.*') ? 'bg-blue-800' : '' }}">
            🗺 Geografía
        </a>
        <a href="{{ route('admin.legal-documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.legal-documents.*') ? 'bg-blue-800' : '' }}">
            📄 Documentos Legales
        </a>
        <a href="{{ route('admin.api-clients.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.api-clients.*') ? 'bg-blue-800' : '' }}">
            🔑 API Clients
        </a>
        <a href="{{ route('admin.audit.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.audit.*') ? 'bg-blue-800' : '' }}">
            📋 Auditoría
        </a>
        <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase">Ayuda</div>
        <a href="{{ route('admin.manual') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-800 {{ request()->routeIs('admin.manual') ? 'bg-blue-800' : '' }}">
            📖 Manual de usuario
        </a>
    </nav>
</aside>

<!-- Main content -->
<div class="lg:pl-64 flex flex-col min-h-screen">
    <!-- Top bar -->
    <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6">
        <button @click="sidebarOpen=true" class="lg:hidden text-gray-500 hover:text-gray-700">
            ☰
        </button>
        <div class="flex items-center gap-4 ml-auto">
            <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">{{ auth()->user()->getRoleNames()->first() }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Salir</button>
            </form>
        </div>
    </header>

    <main class="flex-1 p-6">
        <!-- Flash messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                ✗ {{ session('error') }}
            </div>
        @endif
        @if(session('plain_secret'))
            <div class="mb-4 bg-yellow-50 border-2 border-yellow-400 text-yellow-900 px-4 py-3 rounded-lg">
                <strong>⚠ Guarda este secret, no volverá a mostrarse:</strong>
                <code class="block mt-2 bg-yellow-100 px-3 py-2 rounded font-mono text-sm">{{ session('plain_secret') }}</code>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>