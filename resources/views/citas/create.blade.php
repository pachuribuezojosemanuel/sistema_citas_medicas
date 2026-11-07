<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva cita médica</h2>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/citas.css') }}">

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('citas.store') }}" id="form-cita" data-validar novalidate
                      data-url-medicos="{{ route('citas.medicos') }}"
                      data-url-disponibilidad="{{ route('citas.disponibilidad') }}">
                    @csrf

                    {{-- 1. Paciente --}}
                    <h3 class="font-semibold text-gray-800 mb-3">1. Paciente</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="filtro-paciente" value="Buscar" />
                            <x-text-input id="filtro-paciente" class="block mt-1 w-full" placeholder="CI o nombre…" autocomplete="off" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="paciente_id" value="Paciente *" />
                            <x-select-input id="paciente_id" name="paciente_id" class="block mt-1 w-full" data-reglas="requerido">
                                <option value="">— Seleccione un paciente —</option>
                                @foreach ($pacientes as $p)
                                    <option value="{{ $p->id }}" @selected($pacienteId == $p->id)>
                                        {{ $p->apellidos }}, {{ $p->nombres }} — CI {{ $p->ci }} ({{ $p->codigo }})
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('paciente_id')" class="mt-2" />
                        </div>
                    </div>

                    {{-- 2. Especialidad y médico --}}
                    <h3 class="font-semibold text-gray-800 mt-8 mb-3">2. Especialidad y médico</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="especialidad_id" value="Especialidad *" />
                            <x-select-input id="especialidad_id" name="especialidad_id" class="block mt-1 w-full" data-reglas="requerido">
                                <option value="">— Seleccione —</option>
                                @foreach ($especialidades as $e)
                                    <option value="{{ $e->id }}" @selected(old('especialidad_id') == $e->id)>{{ $e->nombre }}</option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('especialidad_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="medico_id" value="Médico *" />
                            <x-select-input id="medico_id" name="medico_id" class="block mt-1 w-full"
                                            data-reglas="requerido" data-seleccionado="{{ old('medico_id') }}">
                                <option value="">— Primero elija la especialidad —</option>
                            </x-select-input>
                            <x-input-error :messages="$errors->get('medico_id')" class="mt-2" />
                        </div>
                    </div>

                    {{-- 3. Fecha y hora --}}
                    <h3 class="font-semibold text-gray-800 mt-8 mb-3">3. Fecha y hora</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="fecha" value="Fecha *" />
                            <x-text-input id="fecha" name="fecha" type="date" class="block mt-1 w-full"
                                          min="{{ today()->toDateString() }}" :value="old('fecha')" data-reglas="requerido" />
                            <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Horarios disponibles *" />
                            <div id="contenedor-horas" class="mt-2" data-seleccionada="{{ old('hora_inicio') }}">
                                <p class="hora-mensaje">Seleccione médico y fecha para ver los horarios disponibles.</p>
                            </div>
                            <p id="error-hora" class="text-sm text-red-600 mt-1 hidden">Seleccione una hora disponible.</p>
                            <x-input-error :messages="$errors->get('hora_inicio')" class="mt-2" />
                        </div>
                    </div>

                    {{-- 4. Detalle --}}
                    <h3 class="font-semibold text-gray-800 mt-8 mb-3">4. Detalle</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="modalidad" value="Modalidad *" />
                            <x-select-input id="modalidad" name="modalidad" class="block mt-1 w-full">
                                @foreach ($modalidades as $mod)
                                    <option value="{{ $mod }}" @selected(old('modalidad', 'Presencial') === $mod)>{{ $mod }}</option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('modalidad')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="motivo" value="Motivo breve (opcional)" />
                            <x-text-input id="motivo" name="motivo" class="block mt-1 w-full" maxlength="200"
                                          :value="old('motivo')" placeholder="Ej.: Consulta general" data-reglas="max:200" />
                            <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mt-6">
                        * Campos obligatorios. Al guardar, el sistema vuelve a comprobar que el horario siga libre.
                    </p>

                    <div class="flex justify-end gap-3 mt-6">
                        <x-boton-enlace :href="route('citas.index')">Cancelar</x-boton-enlace>
                        <x-primary-button>Registrar cita</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/citas.js') }}"></script>
</x-app-layout>