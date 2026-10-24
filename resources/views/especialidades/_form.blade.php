<div>
    <x-input-label for="nombre" value="Nombre *" />
    <x-text-input id="nombre" name="nombre" class="block mt-1 w-full" maxlength="60"
                  :value="old('nombre', $especialidad->nombre)"
                  placeholder="Ej.: Medicina general" data-reglas="requerido|min:3|max:60" />
    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="descripcion" value="Descripción" />
    <textarea id="descripcion" name="descripcion" rows="3" maxlength="200" data-reglas="max:200"
              class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('descripcion', $especialidad->descripcion) }}</textarea>
    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
</div>

<p class="text-xs text-gray-500 mt-4">* Campo obligatorio. No se permiten nombres repetidos (sin importar mayúsculas ni tildes).</p>