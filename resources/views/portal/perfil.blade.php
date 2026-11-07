<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mi perfil</h2>
            <x-boton-enlace :href="route('profile.edit')">Cambiar contraseña</x-boton-enlace>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Datos personales (solo lectura) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Datos personales</h3>
                <dl class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><dt class="text-gray-500">Nombre</dt><dd class="font-medium">{{ $paciente->nombre_completo }}</dd></div>
                    <div><dt class="text-gray-500">CI</dt><dd class="font-medium">{{ $paciente->ci }}</dd></div>
                    <div><dt class="text-gray-500">Nacimiento</dt><dd class="font-medium">{{ $paciente->fecha_nacimiento->format('d/m/Y') }} ({{ $paciente->edad }} años)</dd></div>
                    <div><dt class="text-gray-500">Correo</dt><dd class="font-medium">{{ $paciente->correo ?? '—' }}</dd></div>
                </dl>
                <p class="mt-3 text-xs text-gray-500">Para corregir su nombre, CI, correo o fecha de nacimiento, comuníquese con recepción.</p>
            </div>

            {{-- Información médica (solo lectura) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Información médica</h3>
                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Tipo de sangre</dt><dd class="font-medium">{{ $paciente->tipoSangre?->nombre ?? 'No registrado' }}</dd></div>
                    <div><dt class="text-gray-500">Alergias</dt><dd class="font-medium">{{ $paciente->alergias->pluck('nombre')->join(', ') ?: 'Ninguna registrada' }}</dd></div>
                    <div><dt class="text-gray-500">Enfermedades crónicas</dt><dd class="font-medium">{{ $paciente->enfermedades->pluck('nombre')->join(', ') ?: 'Ninguna registrada' }}</dd></div>
                    <div><dt class="text-gray-500">Medicamentos</dt><dd class="font-medium whitespace-pre-line">{{ $paciente->medicamentos ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Antecedentes</dt><dd class="font-medium whitespace-pre-line">{{ $paciente->antecedentes ?? '—' }}</dd></div>
                </dl>
            </div>

            {{-- Datos que el paciente puede actualizar --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('mi-perfil.update') }}" data-validar novalidate>
                    @csrf
                    @method('PUT')

                    <h3 class="font-semibold text-gray-800 mb-3">Datos de contacto</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="telefono" value="Teléfono *" />
                            <x-text-input id="telefono" name="telefono" class="block mt-1 w-full" maxlength="20"
                                          :value="old('telefono', $paciente->telefono)" data-reglas="requerido|telefono" />
                            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="direccion" value="Dirección" />
                            <x-text-input id="direccion" name="direccion" class="block mt-1 w-full" maxlength="200"
                                          :value="old('direccion', $paciente->direccion)" data-reglas="max:200" />
                            <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                        </div>
                    </div>

                    @include('pacientes._contactos')

                    <div class="flex justify-end mt-6">
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/pacientes.js') }}"></script>
</x-app-layout>