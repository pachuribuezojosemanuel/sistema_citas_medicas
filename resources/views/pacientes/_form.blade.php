@php($esNuevo = ! $paciente->exists)

{{-- ============ DATOS PERSONALES ============ --}}
<h3 class="font-semibold text-gray-800 mb-3">Datos personales</h3>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    @unless ($esNuevo)
        <div>
            <x-input-label value="Código" />
            <x-text-input class="block mt-1 w-full bg-gray-100" :value="$paciente->codigo" disabled />
        </div>
    @endunless

    <div>
        <x-input-label for="ci" value="CI *" />
        <x-text-input id="ci" name="ci" class="block mt-1 w-full uppercase" maxlength="15"
                      :value="old('ci', $paciente->ci)" placeholder="Ej.: 7845123" data-reglas="requerido|ci" />
        <x-input-error :messages="$errors->get('ci')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nombres" value="Nombres *" />
        <x-text-input id="nombres" name="nombres" class="block mt-1 w-full" maxlength="60"
                      :value="old('nombres', $paciente->nombres)" data-reglas="requerido|min:2|max:60" />
        <x-input-error :messages="$errors->get('nombres')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="apellidos" value="Apellidos *" />
        <x-text-input id="apellidos" name="apellidos" class="block mt-1 w-full" maxlength="60"
                      :value="old('apellidos', $paciente->apellidos)" data-reglas="requerido|min:2|max:60" />
        <x-input-error :messages="$errors->get('apellidos')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento *" />
        <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="block mt-1 w-full"
                      :value="old('fecha_nacimiento', $paciente->fecha_nacimiento?->toDateString())"
                      max="{{ today()->toDateString() }}" data-reglas="requerido" />
        <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="sexo_id" value="Sexo *" />
        <x-select-input id="sexo_id" name="sexo_id" class="block mt-1 w-full" data-reglas="requerido">
            <option value="">— Seleccione —</option>
            @foreach ($sexos as $s)
                <option value="{{ $s->id }}" @selected(old('sexo_id', $paciente->sexo_id) == $s->id)>{{ $s->nombre }}</option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('sexo_id')" class="mt-2" />
    </div>
</div>

{{-- ============ CONTACTO ============ --}}
<h3 class="font-semibold text-gray-800 mt-8 mb-3">Contacto</h3>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="telefono" value="Teléfono *" />
        <x-text-input id="telefono" name="telefono" class="block mt-1 w-full" maxlength="20"
                      :value="old('telefono', $paciente->telefono)" placeholder="Ej.: 71234567" data-reglas="requerido|telefono" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="correo" value="Correo electrónico" />
        <x-text-input id="correo" name="correo" type="email" class="block mt-1 w-full" maxlength="100"
                      :value="old('correo', $paciente->correo)" data-reglas="email|max:100" />
        <x-input-error :messages="$errors->get('correo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="direccion" value="Dirección" />
        <x-text-input id="direccion" name="direccion" class="block mt-1 w-full" maxlength="200"
                      :value="old('direccion', $paciente->direccion)" data-reglas="max:200" />
        <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
    </div>
</div>

{{-- ============ INFORMACIÓN MÉDICA ============ --}}
<h3 class="font-semibold text-gray-800 mt-8 mb-3">Información médica</h3>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="tipo_sangre_id" value="Tipo de sangre" />
        <x-select-input id="tipo_sangre_id" name="tipo_sangre_id" class="block mt-1 w-full">
            <option value="">No registrado</option>
            @foreach ($tiposSangre as $t)
                <option value="{{ $t->id }}" @selected(old('tipo_sangre_id', $paciente->tipo_sangre_id) == $t->id)>{{ $t->nombre }}</option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('tipo_sangre_id')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="medicamentos" value="Medicamentos que toma actualmente" />
        <textarea id="medicamentos" name="medicamentos" rows="2" maxlength="2000"
                  class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('medicamentos', $paciente->medicamentos) }}</textarea>
        <x-input-error :messages="$errors->get('medicamentos')" class="mt-2" />
    </div>

    <div class="sm:col-span-3">
        <x-input-label for="antecedentes" value="Antecedentes médicos relevantes" />
        <textarea id="antecedentes" name="antecedentes" rows="2" maxlength="2000"
                  class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('antecedentes', $paciente->antecedentes) }}</textarea>
        <x-input-error :messages="$errors->get('antecedentes')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    {{-- Alergias --}}
    <fieldset class="border border-gray-200 rounded-md p-4">
        <legend class="px-1 text-sm font-semibold text-gray-700">Alergias</legend>
        @foreach ($alergias as $a)
            @php($marcada = in_array($a->id, $alergiasMarcadas, true))
            <div class="flex items-center gap-2 py-1">
                <label class="flex items-center gap-2 w-1/2 text-sm text-gray-700">
                    <input type="checkbox" id="alergia-{{ $a->id }}" name="alergias[]" value="{{ $a->id }}"
                           @checked($marcada) class="js-habilita rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    {{ $a->nombre }}
                </label>
                <x-text-input name="obs_alergia[{{ $a->id }}]" data-depende="alergia-{{ $a->id }}"
                              class="w-1/2 text-sm py-1" placeholder="Observación (ej.: reacción severa)" maxlength="200"
                              :value="old('obs_alergia.' . $a->id, $obsAlergias[$a->id] ?? '')" :disabled="! $marcada" />
            </div>
        @endforeach
    </fieldset>

    {{-- Enfermedades crónicas --}}
    <fieldset class="border border-gray-200 rounded-md p-4">
        <legend class="px-1 text-sm font-semibold text-gray-700">Enfermedades crónicas</legend>
        @foreach ($enfermedades as $e)
            @php($marcada = in_array($e->id, $enfermedadesMarcadas, true))
            <div class="py-1">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" id="enfermedad-{{ $e->id }}" name="enfermedades[]" value="{{ $e->id }}"
                           @checked($marcada) class="js-habilita rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    {{ $e->nombre }}
                </label>
                <div class="grid grid-cols-3 gap-2 mt-1 ms-6">
                    <x-text-input type="date" name="diag_enfermedad[{{ $e->id }}]" data-depende="enfermedad-{{ $e->id }}"
                                  class="text-sm py-1" max="{{ today()->toDateString() }}" title="Fecha de diagnóstico"
                                  :value="old('diag_enfermedad.' . $e->id, $diagEnfermedades[$e->id] ?? '')" :disabled="! $marcada" />
                    <x-text-input name="obs_enfermedad[{{ $e->id }}]" data-depende="enfermedad-{{ $e->id }}"
                                  class="col-span-2 text-sm py-1" placeholder="Observación" maxlength="200"
                                  :value="old('obs_enfermedad.' . $e->id, $obsEnfermedades[$e->id] ?? '')" :disabled="! $marcada" />
                </div>
            </div>
        @endforeach
        <x-input-error :messages="$errors->first('diag_enfermedad.*')" class="mt-2" />
    </fieldset>
</div>

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

{{-- ============ CUENTA DE ACCESO (OPCIONAL) ============ --}}
<h3 class="font-semibold text-gray-800 mt-8 mb-3">Cuenta de acceso al portal</h3>

@if ($paciente->user)
    <p class="text-sm text-gray-600">
        Tiene cuenta: <strong>{{ $paciente->user->name }}</strong>.
        La contraseña se cambia desde <em>Administración → Usuarios</em>.
    </p>
@else
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" id="crear_cuenta" name="crear_cuenta" value="1" @checked(old('crear_cuenta'))
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        Crear cuenta para que el paciente consulte y reserve sus citas (requiere correo)
    </label>

    <div id="seccion-cuenta" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3 hidden">
        <div>
            <x-input-label for="name" value="Nombre de usuario *" />
            <x-text-input id="name" name="name" class="campo-cuenta block mt-1 w-full" maxlength="50" autocomplete="off"
                          :value="old('name')" data-reglas="requerido|min:3|max:50|usuario" disabled />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password" value="Contraseña *" />
            <x-text-input id="password" name="password" type="password" class="campo-cuenta block mt-1 w-full"
                          autocomplete="new-password" data-reglas="requerido|min:8|clave" disabled />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña *" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                          class="campo-cuenta block mt-1 w-full" autocomplete="new-password" data-reglas="coincide:password" disabled />
        </div>
    </div>
@endif

<p class="text-xs text-gray-500 mt-6">* Campos obligatorios.</p>

<script src="{{ asset('js/pacientes.js') }}"></script>