{{-- Botones: generar, exportar CSV e imprimir / guardar PDF --}}
<div class="flex flex-wrap gap-2">
    <x-primary-button>Generar</x-primary-button>
    <button type="submit" name="exportar" value="csv"
            class="inline-flex items-center px-4 py-2 bg-green-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
        CSV
    </button>
    <button type="button" data-imprimir
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
        Imprimir / PDF
    </button>
</div>