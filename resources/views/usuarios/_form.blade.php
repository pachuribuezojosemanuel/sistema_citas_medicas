@php($esNuevo = ! $usuario->exists)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Nombre de usuario --}}
    <div>
        <x-input-label for="name" value="Nombre de usuario *" />
        <x-text-input id="name" name="name" class="block mt-1 w-full" maxlength="50"
                      :value="old('name', $usuario->name)"
                      data-reglas="requerido|min:3|max:50|usuario" autocomplete="off" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    {{-- Correo --}}
    <div>
        <x-input-label for="email" value="Correo electrónico *" />
        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" maxlength="100"
                      :value="old('email', $usuario->email)"
                      data-reglas="requerido|email|max:100" autocomplete="off" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    {{-- Rol --}}
    <div class="sm:col-span-2">
        <x-input-label for="rol_id" value="Rol *" />
        <x-select-input id="rol_id" name="rol_id" class="block mt-1 w-full" data-reglas="requerido">
            <option value="">— Seleccione un rol —</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id }}" @selected(old('rol_id', $usuario->rol_id) == $rol->id)>
                    {{ $rol->nombre }}
                </option>
            @endforeach
        </x-select-input>
        <x-input-error :messages="$errors->get('rol_id')" class="mt-2" />
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

    {{-- Confirmación --}}
    <div>
        <x-input-label for="password_confirmation" :value="$esNuevo ? 'Confirmar contraseña *' : 'Confirmar nueva contraseña'" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full"
                      data-reglas="coincide:password" autocomplete="new-password" />
    </div>
</div>

<p class="text-xs text-gray-500 mt-4">* Campos obligatorios. La contraseña debe tener al menos 8 caracteres, con letras y números.</p>