<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reprogramar cita {{ $cita->codigo }}</h2>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/citas.css') }}">

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                {{-- Datos actuales --}}
                <div class="rounded-md bg-gray-50 border border-gray-200 p-4 text-sm">
                    <p><strong>Paciente:</strong> {{ $cita->paciente->nombre_completo }} (CI {{ $cita->paciente->ci }})</p>
                    <p><strong>Médico:</strong> {{ $cita->medico->nombre_completo }} · {{ $cita->especialidad->nombre }}</p>
                    <p><strong>Fecha y hora actuales:</strong> {{ ucfirst($cita->fecha->translatedFormat('l d/m/Y')) }}, {{ $cita->horario }}</p>
                    <p><strong>Estado:</strong> {{ $cita->estado->nombre }} (se mantiene)</p>
                </div>

                <form method="POST" action="{{ route('citas.reprogramar.guardar', $cita) }}" id="form-cita" class="mt-6"
                      data-validar novalidate
                      data-url-disponibilidad="{{ route('citas.disponibilidad') }}"
                      data-ignorar="{{ $cita->id }}">
                    @csrf
                    @method('PUT')

                    {{-- Mismo médico --}}
                    <input type="hidden" id="medico_id" value="{{ $cita->medico_id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="fecha" value="Nueva fecha *" />
                            <x-text-input id="fecha" name="fecha" type="date" class="block mt-1 w-full"
                                          min="{{ today()->toDateString() }}"
                                          :value="old('fecha', $cita->fecha->toDateString())" data-reglas="requerido" />
                            <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Horarios disponibles *" />
                            <div id="contenedor-horas" class="mt-2" data-seleccionada="{{ old('hora_inicio') }}"></div>
                            <p id="error-hora" class="text-sm text-red-600 mt-1 hidden">Seleccione una hora disponible.</p>
                            <x-input-error :messages="$errors->get('hora_inicio')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label for="motivo" value="Motivo de la reprogramación *" />
                            <x-text-input id="motivo" name="motivo" class="block mt-1 w-full" maxlength="200"
                                          :value="old('motivo')" placeholder="Ej.: El paciente tiene un viaje"
                                          data-reglas="requerido|min:5|max:200" />
                            <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mt-4">El horario actual de la cita aparece como disponible para poder elegir otra hora el mismo día.</p>

                    <div class="flex justify-end gap-3 mt-6">
                        <x-boton-enlace :href="route('citas.show', $cita)">Volver</x-boton-enlace>
                        <x-primary-button>Guardar reprogramación</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/citas.js') }}"></script>
</x-app-layout>