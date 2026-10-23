@php($marcados = collect(old('permisos', $seleccionados))->map(fn ($id) => (int) $id)->all())

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="nombre" value="Nombre del rol *" />
        <x-text-input id="nombre" name="nombre" class="block mt-1 w-full" maxlength="30"
                      :value="old('nombre', $rol->nombre)" data-reglas="requerido|max:30" />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="descripcion" value="Descripción" />
        <x-text-input id="descripcion" name="descripcion" class="block mt-1 w-full" maxlength="150"
                      :value="old('descripcion', $rol->descripcion)" data-reglas="max:150" />
        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
    </div>
</div>

<div class="mt-6">
    <h3 class="font-semibold text-gray-800">Permisos</h3>
    <p class="text-sm text-gray-500">Marque las acciones que podrán realizar los usuarios con este rol.</p>
    <x-input-error :messages="$errors->first('permisos.*')" class="mt-2" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
        @foreach ($permisos as $modulo => $lista)
            <fieldset class="border border-gray-200 rounded-md p-3">
                <legend class="px-1 text-sm font-semibold text-gray-700">{{ $modulo }}</legend>

                @foreach ($lista as $permiso)
                    <label class="flex items-start gap-2 py-1 text-sm text-gray-700">
                        <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                               @checked(in_array($permiso->id, $marcados, true))
                               class="mt-0.5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span>
                            {{ $permiso->nombre }}
                            <span class="text-xs text-gray-400">({{ $permiso->codigo }})</span>
                        </span>
                    </label>
                @endforeach
            </fieldset>
        @endforeach
    </div>
</div>