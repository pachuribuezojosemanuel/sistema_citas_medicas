@php($esNuevo = ! $usuario->exists)

{{-- ============ CUENTA DE ACCESO ============ --}}
<h3 class="font-semibold text-gray-800 mb-3">Cuenta de acceso</h3>

@if ($vinculo)
    <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
        Esta es la cuenta del {{ $vinculo['tipo'] }} <strong>{{ $vinculo['nombre'] }}</strong>.
        Aquí solo puede cambiar el nombre de usuario y la contraseña.
        Su correo, rol y datos personales se editan en
        @if ($vinculo['url'])
            <a href="{{ $vinculo['url'] }}" class="font-semibold underline">{{ $vinculo['modulo'] }}</a>.
        @else
            {{ $vinculo['modulo'] }}.
        @endif
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Nombre de usuario --}}
    <div>
        <x-input-label for="name" value="Nombre de usuario *" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" maxlength="50" autocomplete="off"
                      :value="old('name', $usuario->name)" data-reglas="requerido|min:3|max:50|usuario" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    {{-- Correo --}}
    <div>
        <x-input-label for="email" value="Correo electrónico *" />
        @if ($vinculo)
            <x-text-input id="email" class="block mt-1 w-full bg-gray-100" :value="$usuario->email" disabled />
        @else
            <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" maxlength="100" autocomplete="off"
                          :value="old('email', $usuario->email)" data-reglas="requerido|email|max:100" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        @endif
    </div>

    {{-- Rol --}}
    <div class="sm:col-span-2">
        <x-input-label for="rol_id" value="Rol *" />
        @if ($vinculo)
            <x-text-input class="block mt-1 w-full bg-gray-100" :value="$usuario->rol->nombre" disabled />
        @else
            <x-select-input id="rol_id" name="rol_id" class="block mt-1 w-full" data-reglas="requerido">
                <option value="">— Seleccione un rol —</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id }}" @selected(old('rol_id', $usuario->rol_id) == $rol->id)>{{ $rol->nombre }}</option>
                @endforeach
            </x-select-input>
            <p class="text-xs text-gray-500 mt-1">Médicos y pacientes se registran en sus propios módulos.</p>
            <x-input-error :messages="$errors->get('rol_id')" class="mt-2" />
        @endif
    </div>

    {{-- Contraseña --}}
    <div>
        <x-input-label for="password" :value="$esNuevo ? 'Contraseña *' : 'Nueva contraseña'" />
        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full"
                      data-reglas="{{ $esNuevo ? 'requerido|' : '' }}min:8|clave" autocomplete="new-password" />
        @unless ($esNuevo)
            <p class="text-xs text-gray-500 mt-1">Déjela en blanco para mantener la contraseña actual.</p>
        @endunless
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="$esNuevo ? 'Confirmar contraseña *' : 'Confirmar nueva contraseña'" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full"
                      data-reglas="coincide:password" autocomplete="new-password" />
    </div>
</div>

{{-- ============ DATOS DEL PERSONAL ============ --}}
@unless ($vinculo)
    <h3 class="font-semibold text-gray-800 mt-8 mb-3">Datos del personal</h3>

    @if (! $esNuevo && ! $personal->exists)
        <p class="mb-3 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-3">
            Este usuario todavía no tiene datos personales registrados. Complételos para guardar.
        </p>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="ci" value="CI *" />
            <x-text-input id="ci" name="ci" class="block mt-1 w-full uppercase" maxlength="15"
                          :value="old('ci', $personal->ci)" data-reglas="requerido|ci" />
            <x-input-error :messages="$errors->get('ci')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="nombres" value="Nombres *" />
            <x-text-input id="nombres" name="nombres" class="block mt-1 w-full" maxlength="60"
                          :value="old('nombres', $personal->nombres)" data-reglas="requerido|min:2|max:60" />
            <x-input-error :messages="$errors->get('nombres')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="apellidos" value="Apellidos *" />
            <x-text-input id="apellidos" name="apellidos" class="block mt-1 w-full" maxlength="60"
                          :value="old('apellidos', $personal->apellidos)" data-reglas="requerido|min:2|max:60" />
            <x-input-error :messages="$errors->get('apellidos')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="telefono" value="Teléfono" />
            <x-text-input id="telefono" name="telefono" class="block mt-1 w-full" maxlength="20"
                          :value="old('telefono', $personal->telefono)" data-reglas="telefono" />
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="cargo" value="Cargo *" />
            <x-text-input id="cargo" name="cargo" class="block mt-1 w-full" maxlength="50" list="lista-cargos"
                          :value="old('cargo', $personal->cargo)" data-reglas="requerido|min:3|max:50" />
            <datalist id="lista-cargos">
                <option value="Administrador del sistema">
                <option value="Recepcionista">
                <option value="Jefe de recepción">
                <option value="Encargado de admisión">
            </datalist>
            <x-input-error :messages="$errors->get('cargo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="fecha_ingreso" value="Fecha de ingreso" />
            <x-text-input id="fecha_ingreso" name="fecha_ingreso" type="date" class="block mt-1 w-full"
                          max="{{ today()->toDateString() }}"
                          :value="old('fecha_ingreso', $personal->fecha_ingreso?->toDateString())" />
            <x-input-error :messages="$errors->get('fecha_ingreso')" class="mt-2" />
        </div>
    </div>

    <script src="{{ asset('js/usuarios.js') }}"></script>
@endunless

<p class="text-xs text-gray-500 mt-4">* Campos obligatorios. La contraseña debe tener al menos 8 caracteres, con letras y números.</p>