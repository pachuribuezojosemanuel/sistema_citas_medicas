@props(['estado'])

@php
    $colores = [
        'Programada'   => 'bg-blue-100 text-blue-800',
        'Confirmada'   => 'bg-indigo-100 text-indigo-800',
        'Atendida'     => 'bg-green-100 text-green-800',
        'Cancelada'    => 'bg-red-100 text-red-800',
        'Inasistencia' => 'bg-amber-100 text-amber-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold ' . ($colores[$estado] ?? 'bg-gray-100 text-gray-700')]) }}>
    {{ $estado }}
</span>