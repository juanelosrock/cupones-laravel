<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — CuponesHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-900 to-blue-700 min-h-screen flex items-center justify-center">
<div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="text-4xl mb-2">🎟</div>
            <h1 class="text-2xl font-bold text-gray-900">CuponesHub</h1>
            <p class="text-gray-500 text-sm mt-1">Panel de Administración</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="mr-2">
                    <label for="remember" class="text-sm text-gray-600">Recordarme</label>
                </div>
            </div>
            <button type="submit"
                    class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors">
                Iniciar Sesión
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-100 text-center text-xs text-gray-400">
            <a href="{{ route('public.legal.terms') }}" class="hover:underline">Términos</a> ·
            <a href="{{ route('public.legal.privacy') }}" class="hover:underline">Privacidad</a>
        </div>
    </div>
</div>
</body>
</html>