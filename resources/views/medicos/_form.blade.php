@php
    $esNuevo  = ! $medico->exists;
    $marcadas = collect(old('especialidades', $seleccionadas))->map(fn ($id) => (int) $id)->all();
@endphp

{{-- Datos del médico --}}
<h3 class="font-semibold text-gray-800 mb-3">Datos del médico</h3>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @unless ($esNuevo)
        <div>
            <x-input-label value="Código" />
            <x-text-input class="block mt-1 w-full bg-gray-100" :value="$medico->codigo" disabled />
        </div>
    @endunless

    <div>
        <x-input-label for="registro_profesional" value="Registro profesional *" />
        <x-text-input id="registro_profesional" name="registro_profesional" class="block mt-1 w-full uppercase" maxlength="30"
                      :value="old('registro_profesional', $medico->registro_profesional)" placeholder="Ej.: MP-12345"
                      data-reglas="requerido|max:30" />
        <x-input-error :messages="$errors->get('registro_profesional')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nombres" value="Nombres *" />
        <x-text-input id="nombres" name="nombres" class="block mt-1 w-full" maxlength="60"
                      :value="old('nombres', $medico->nombres)" data-reglas="requerido|min:2|max:60" />
        <x-input-error :messages="$errors->get('nombres')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="apellidos" value="Apellidos *" />
        <x-text-input id="apellidos" name="apellidos" class="block mt-1 w-full" maxlength="60"
                      :value="old('apellidos', $medico->apellidos)" data-reglas="requerido|min:2|max:60" />
        <x-input-error :messages="$errors->get('apellidos')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono *" />
        <x-text-input id="telefono" name="telefono" class="block mt-1 w-full" maxlength="20"
                      :value="old('telefono', $medico->telefono)" placeholder="Ej.: 70012345" data-reglas="requerido|telefono" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="correo" value="Correo electrónico *" />
        <x-text-input id="correo" name="correo" type="email" class="block mt-1 w-full" maxlength="100"
                      :value="old('correo', $medico->correo)" data-reglas="requerido|email|max:100" />
        <p class="text-xs text-gray-500 mt-1">También es el correo de su cuenta de acceso.</p>
        <x-input-error :messages="$errors->get('correo')" class="mt-2" />
    </div>
</div>

{{-- Especialidades (al menos una) --}}
<h3 class="font-semibold text-gray-800 mt-6 mb-1">Especialidades *</h3>
<p class="text-sm text-gray-500 mb-2">Seleccione una o varias.</p>

<div class="grid grid-cols-2 sm:grid-cols-3 gap-2 border border-gray-200 rounded-md p-3">
    @foreach ($especialidades as $esp)
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="especialidades[]" value="{{ $esp->id }}"
                   @checked(in_array($esp->id, $marcadas, true))
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            {{ $esp->nombre }}
            @unless ($esp->estado)
                <span class="text-xs text-red-500">(inactiva)</span>
            @endunless
        </label>
    @endforeach
</div>
<x-input-error :messages="$errors->get('especialidades')" class="mt-2" />
<p class="text-sm text-red-600 mt-2 hidden" id="error-especialidades">Seleccione al menos una especialidad.</p>

{{-- Cuenta de acceso: solo al registrar --}}
@if ($esNuevo)
    <h3 class="font-semibold text-gray-800 mt-6 mb-3">Cuenta de acceso al sistema</h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="name" value="Nombre de usuario *" />
            <x-text-input id="name" name="name" class="block mt-1 w-full" maxlength="50" autocomplete="off"
                          :value="old('name')" placeholder="Ej.: dr.perez" data-reglas="requerido|min:3|max:50|usuario" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña *" />
            <x-text-input id="password" name="password" type="password" class="block mt-1 w-full"
                          autocomplete="new-password" data-reglas="requerido|min:8|clave" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña *" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full"
                          autocomplete="new-password" data-reglas="coincide:password" />
        </div>
    </div>
@else
    <p class="text-sm text-gray-500 mt-6">
        Cuenta de acceso: <strong>{{ $medico->user?->name }}</strong>.
        La contraseña se cambia desde <em>Administración → Usuarios</em>.
    </p>
@endif

<p class="text-xs text-gray-500 mt-4">* Campos obligatorios.</p>

<script src="{{ asset('js/medicos.js') }}"></script>