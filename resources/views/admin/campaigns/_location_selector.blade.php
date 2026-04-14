<div class="bg-white rounded-xl shadow-sm p-6" x-data="locationSelector()">
    <h2 class="text-base font-semibold text-gray-800 mb-1">Cobertura geográfica</h2>
    <p class="text-xs text-gray-500 mb-4">
        Asocia zonas o puntos de venta para medir interacciones por ubicación.
        <span class="text-gray-400">(Opcional — puedes modificarlo luego.)</span>
    </p>

    {{-- Tabs zonas / PDV --}}
    <div class="flex gap-1 bg-gray-100 rounded-lg p-1 w-fit mb-4">
        <button type="button" @click="tab='zones'"
                :class="tab==='zones' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-3 py-1.5 rounded-md text-xs transition-all">
            Zonas
            <span x-show="selectedZones.length > 0"
                  class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold"
                  x-text="selectedZones.length"></span>
        </button>
        <button type="button" @click="tab='pos'"
                :class="tab==='pos' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-3 py-1.5 rounded-md text-xs transition-all">
            Puntos de venta
            <span x-show="selectedPOS.length > 0"
                  class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px] font-bold"
                  x-text="selectedPOS.length"></span>
        </button>
    </div>

    {{-- Buscador --}}
    <div class="relative mb-3">
        <input type="text" x-model="search" placeholder="Buscar..."
               class="w-full border border-gray-200 rounded-lg pl-8 pr-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
        <svg class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>

    {{-- Lista zonas --}}
    <div x-show="tab === 'zones'" class="max-h-52 overflow-y-auto border border-gray-100 rounded-lg divide-y divide-gray-50">
        @php $zonesByCity = $zones->groupBy(fn($z) => $z->city->name); @endphp
        @forelse($zonesByCity as $cityName => $cityZones)
            <div>
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 sticky top-0">{{ $cityName }}</p>
                @foreach($cityZones as $zone)
                <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-blue-50/60 cursor-pointer transition-colors"
                       x-show="!search || '{{ strtolower($zone->name) }}'.includes(search.toLowerCase())">
                    <input type="checkbox"
                           name="zone_ids[]"
                           value="{{ $zone->id }}"
                           x-model="selectedZones"
                           :value="'{{ $zone->id }}'"
                           {{ in_array($zone->id, (array)($selectedZones ?? [])) ? 'checked' : '' }}
                           class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs text-gray-700">{{ $zone->name }}</span>
                </label>
                @endforeach
            </div>
        @empty
            <p class="px-3 py-4 text-xs text-gray-400 text-center">No hay zonas activas disponibles.</p>
        @endforelse
    </div>

    {{-- Lista PDVs --}}
    <div x-show="tab === 'pos'" class="max-h-52 overflow-y-auto border border-gray-100 rounded-lg divide-y divide-gray-50">
        @php $posByCity = $pointsOfSale->groupBy(fn($p) => $p->city->name); @endphp
        @forelse($posByCity as $cityName => $cityPOS)
            <div>
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 sticky top-0">{{ $cityName }}</p>
                @foreach($cityPOS as $pos)
                <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-blue-50/60 cursor-pointer transition-colors"
                       x-show="!search || '{{ strtolower($pos->name.' '.$pos->code) }}'.includes(search.toLowerCase())">
                    <input type="checkbox"
                           name="pos_ids[]"
                           value="{{ $pos->id }}"
                           x-model="selectedPOS"
                           :value="'{{ $pos->id }}'"
                           {{ in_array($pos->id, (array)($selectedPOS ?? [])) ? 'checked' : '' }}
                           class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="text-xs text-gray-700">{{ $pos->name }}</span>
                        <span class="ml-1 text-[10px] font-mono text-gray-400">{{ $pos->code }}</span>
                        @if($pos->zone)
                            <span class="ml-1 text-[10px] text-gray-400">· {{ $pos->zone->name }}</span>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
        @empty
            <p class="px-3 py-4 text-xs text-gray-400 text-center">No hay puntos de venta activos disponibles.</p>
        @endforelse
    </div>

    {{-- Resumen seleccionados --}}
    <template x-if="selectedZones.length > 0 || selectedPOS.length > 0">
        <div class="mt-3 p-2.5 bg-blue-50 border border-blue-100 rounded-lg">
            <p class="text-xs text-blue-700">
                <template x-if="selectedZones.length > 0">
                    <span><strong x-text="selectedZones.length"></strong> zona(s)</span>
                </template>
                <template x-if="selectedZones.length > 0 && selectedPOS.length > 0">
                    <span> · </span>
                </template>
                <template x-if="selectedPOS.length > 0">
                    <span><strong x-text="selectedPOS.length"></strong> punto(s) de venta</span>
                </template>
                <span> seleccionados</span>
            </p>
        </div>
    </template>
</div>

<script>
function locationSelector() {
    return {
        tab: 'zones',
        search: '',
        selectedZones: @json(array_map('strval', (array)($selectedZones ?? []))),
        selectedPOS:   @json(array_map('strval', (array)($selectedPOS ?? []))),
    };
}
</script>
