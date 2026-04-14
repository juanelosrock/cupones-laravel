<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — CuponesHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<div class="max-w-3xl mx-auto px-4 py-12">
    <div class="text-center mb-8">
        <a href="/" class="text-2xl font-bold text-blue-700">🎟 CuponesHub</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">{{ $title }}</h1>
        @if($doc)
        <p class="text-sm text-gray-500 mt-2">Versión {{ $doc->version }} · Publicado {{ $doc->published_at?->format('d/m/Y') }}</p>
        @endif
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-8">
        @if($doc)
        <div class="prose max-w-none text-gray-700 text-sm leading-relaxed">
            {!! $doc->content !!}
        </div>
        @else
        <p class="text-gray-500 text-center py-8">Documento no disponible actualmente.</p>
        @endif
    </div>
    <div class="text-center mt-6 text-sm text-gray-400">
        <a href="{{ route('public.legal.terms') }}" class="hover:underline mr-3">Términos</a>
        <a href="{{ route('public.legal.privacy') }}" class="hover:underline mr-3">Privacidad</a>
        <a href="{{ route('public.legal.sms') }}" class="hover:underline">Consentimiento SMS</a>
    </div>
</div>
</body>
</html>