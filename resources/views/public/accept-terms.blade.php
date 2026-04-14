<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aceptar {{ $doc->title }} — CuponesHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4">
<div class="w-full max-w-lg">
    <div class="text-center mb-6">
        <a href="/" class="text-xl font-bold text-blue-700">🎟 CuponesHub</a>
    </div>
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-center">
        {{ session('success') }}
    </div>
    @endif
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <h1 class="text-xl font-bold text-gray-900 mb-2">{{ $doc->title }}</h1>
        <p class="text-xs text-gray-500 mb-6">Versión {{ $doc->version }}</p>
        <div class="bg-gray-50 rounded-xl p-4 max-h-64 overflow-y-auto text-sm text-gray-700 mb-6 leading-relaxed">
            {!! $doc->content !!}
        </div>
        <form method="POST" action="{{ route('public.legal.accept.store', $type) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de documento *</label>
                    <input type="text" name="document_number" required value="{{ old('document_number') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('document_number') border-red-400 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de celular *</label>
                    <input type="tel" name="phone" required value="{{ old('phone') }}" placeholder="+573001234567"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('phone') border-red-400 @enderror">
                </div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="accept" value="1" required class="mt-1 rounded">
                    <span class="text-sm text-gray-700">He leído y acepto los términos del documento <strong>{{ $doc->title }}</strong> (v{{ $doc->version }}). Entiendo que esta aceptación queda registrada con mi IP y fecha.</span>
                </label>
                @error('accept')<p class="text-red-500 text-xs">Debes aceptar para continuar.</p>@enderror
            </div>
            <button type="submit" class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg">
                Confirmar Aceptación
            </button>
        </form>
    </div>
</div>
</body>
</html>