<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupón no encontrado — CuponesHub</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="text-center">
    <div class="text-6xl mb-4">😕</div>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Cupón no encontrado</h1>
    <p class="text-gray-500">El código <code class="bg-gray-100 px-2 py-1 rounded">{{ $code }}</code> no existe o ha sido cancelado.</p>
    <a href="{{ route('public.legal.terms') }}" class="mt-6 inline-block text-sm text-blue-600 hover:underline">Ver términos y condiciones</a>
</div>
</body>
</html>