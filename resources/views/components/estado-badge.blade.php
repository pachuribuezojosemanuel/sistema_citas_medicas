@props(['activo'])

<span {{ $attributes->merge(['class' => 'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold ' . ($activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')]) }}>
    {{ $activo ? 'Activo' : 'Inactivo' }}
</span>