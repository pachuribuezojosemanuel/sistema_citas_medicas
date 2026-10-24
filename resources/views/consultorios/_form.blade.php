<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="numero" value="Número *" />
        <x-text-input id="numero" name="numero" class="block mt-1 w-full uppercase" maxlength="10"
                      :value="old('numero', $consultorio->numero)" placeholder="Ej.: C-101"
                      data-reglas="requerido|max:10" />
        <x-input-error :messages="$errors->get('numero')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="nombre" value="Nombre *" />
        <x-text-input id="nombre" name="nombre" class="block mt-1 w-full" maxlength="50"
                      :value="old('nombre', $consultorio->nombre)" placeholder="Ej.: Consultorio Pediatría"
                      data-reglas="requerido|min:3|max:50" />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="piso" value="Piso" />
        <x-text-input id="piso" name="piso" class="block mt-1 w-full" maxlength="10"
                      :value="old('piso', $consultorio->piso)" placeholder="Ej.: 1" data-reglas="max:10" />
        <x-input-error :messages="$errors->get('piso')" class="mt-2" />
    </div>
</div>

<p class="text-xs text-gray-500 mt-4">* Campos obligatorios. El número de consultorio no puede repetirse.</p>