{{-- ============ CONTACTOS DE EMERGENCIA ============ --}}
<div class="flex items-center justify-between mt-8 mb-3">
    <h3 class="font-semibold text-gray-800">Contactos de emergencia <span class="text-sm font-normal text-gray-500">(hasta 3)</span></h3>
    <button type="button" id="agregar-contacto" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 disabled:text-gray-400">
        + Agregar contacto
    </button>
</div>
<x-input-error :messages="$errors->get('contactos')" class="mb-2" />

<div id="lista-contactos" class="space-y-3" data-siguiente="{{ count($contactos) }}">
    @foreach ($contactos as $i => $c)
        <div class="fila-contacto grid grid-cols-1 sm:grid-cols-12 gap-2 items-start">
            <div class="sm:col-span-5">
                <x-text-input name="contactos[{{ $i }}][nombre_completo]" class="block w-full" maxlength="100"
                              placeholder="Nombre completo" :value="$c['nombre_completo'] ?? ''" data-reglas="requerido|max:100" />
                <x-input-error :messages="$errors->get('contactos.' . $i . '.nombre_completo')" class="mt-1" />
            </div>
            <div class="sm:col-span-3">
                <x-text-input name="contactos[{{ $i }}][parentesco]" class="block w-full" maxlength="30"
                              placeholder="Parentesco" :value="$c['parentesco'] ?? ''" data-reglas="requerido|max:30" />
                <x-input-error :messages="$errors->get('contactos.' . $i . '.parentesco')" class="mt-1" />
            </div>
            <div class="sm:col-span-3">
                <x-text-input name="contactos[{{ $i }}][telefono]" class="block w-full" maxlength="20"
                              placeholder="Teléfono" :value="$c['telefono'] ?? ''" data-reglas="requerido|telefono" />
                <x-input-error :messages="$errors->get('contactos.' . $i . '.telefono')" class="mt-1" />
            </div>
            <div class="sm:col-span-1 pt-2">
                <button type="button" class="quitar-contacto text-sm text-red-600 hover:text-red-800">Quitar</button>
            </div>
        </div>
    @endforeach
</div>

{{-- Plantilla para nuevas filas (la usa pacientes.js) --}}
<template id="plantilla-contacto">
    <div class="fila-contacto grid grid-cols-1 sm:grid-cols-12 gap-2 items-start">
        <div class="sm:col-span-5">
            <input name="contactos[__i__][nombre_completo]" maxlength="100" placeholder="Nombre completo" data-reglas="requerido|max:100"
                   class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        </div>
        <div class="sm:col-span-3">
            <input name="contactos[__i__][parentesco]" maxlength="30" placeholder="Parentesco" data-reglas="requerido|max:30"
                   class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        </div>
        <div class="sm:col-span-3">
            <input name="contactos[__i__][telefono]" maxlength="20" placeholder="Teléfono" data-reglas="requerido|telefono"
                   class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        </div>
        <div class="sm:col-span-1 pt-2">
            <button type="button" class="quitar-contacto text-sm text-red-600 hover:text-red-800">Quitar</button>
        </div>
    </div>
</template>