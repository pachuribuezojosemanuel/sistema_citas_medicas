<div class="solo-imprimir mb-4">
    <div class="text-lg font-semibold">Clínica Vida Salud</div>
    <div class="text-base">{{ $titulo }}</div>
    <div class="text-sm text-gray-600">{{ $periodo }}</div>
    <div class="text-xs text-gray-500">Generado el {{ now()->format('d/m/Y H:i') }} por {{ Auth::user()->name }}</div>
</div>