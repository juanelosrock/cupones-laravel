<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupón {{ $code }} — CuponesHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-900 to-blue-700 min-h-screen flex items-center justify-center py-12 px-4">
<div class="w-full max-w-md" x-data="couponCheck()">
    <div class="text-center mb-6 text-white">
        <div class="text-4xl mb-2">🎟</div>
        <h1 class="text-2xl font-bold">CuponesHub</h1>
    </div>

    <!-- Coupon card -->
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 p-6 text-white text-center">
            <div class="text-sm opacity-80 mb-1">Tu cupón de descuento</div>
            <div class="text-3xl font-bold tracking-widest">{{ $code }}</div>
            @if($batch)
            <div class="mt-2 text-4xl font-extrabold">
                {{ $batch->discount_type === 'percentage' ? $batch->discount_value.'%' : '$'.number_format($batch->discount_value,0,',','.') }}
                <span class="text-lg font-normal">OFF</span>
            </div>
            @endif
        </div>
        <div class="p-6">
            @if($batch)
            <div class="grid grid-cols-2 gap-3 text-sm mb-6">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <div class="text-xs text-gray-500">Válido desde</div>
                    <div class="font-semibold">{{ $batch->start_date->format('d/m/Y') }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <div class="text-xs text-gray-500">Válido hasta</div>
                    <div class="font-semibold text-red-600">{{ $batch->end_date->format('d/m/Y') }}</div>
                </div>
                @if($batch->min_purchase_amount > 0)
                <div class="col-span-2 bg-yellow-50 rounded-lg p-3 text-center">
                    <div class="text-xs text-yellow-700">Compra mínima requerida</div>
                    <div class="font-bold text-yellow-800">${{ number_format($batch->min_purchase_amount,0,',','.') }}</div>
                </div>
                @endif
            </div>
            @endif

            <!-- Verificador en línea -->
            <div class="border-t pt-4">
                <p class="text-sm text-gray-600 mb-3 font-medium">Verificar descuento</p>
                <div class="flex gap-2">
                    <input type="number" x-model="amount" placeholder="Valor de compra $"
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button @click="check()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Calcular
                    </button>
                </div>
                <div x-show="result" class="mt-3 p-3 rounded-lg" :class="result?.valid ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                    <div x-text="result?.message" class="text-sm font-medium"></div>
                    <template x-if="result?.valid">
                        <div class="mt-2 text-sm">
                            <div>Descuento: <strong x-text="'$'+formatNum(result.discount_amount)"></strong></div>
                            <div>Pagas: <strong x-text="'$'+formatNum(result.final_amount)"></strong></div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-4 text-xs text-center text-gray-400">
                <a href="{{ route('public.legal.terms') }}">Términos y condiciones</a> aplicables
            </div>
        </div>
    </div>
</div>
<script>
function couponCheck() {
    return {
        amount: '',
        result: null,
        async check() {
            if (!this.amount) return;
            const r = await fetch('{{ route('public.coupon.check') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({code: '{{ $code }}', amount: this.amount})
            });
            this.result = await r.json();
        },
        formatNum(n) { return Number(n).toLocaleString('es-CO'); }
    }
}
</script>
</body>
</html>