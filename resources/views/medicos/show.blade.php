<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $medico->nombre_completo }}
                <span class="text-sm font-normal text-gray-500">({{ $medico->codigo }})</span>
            </h2>
            <div class="flex gap-2">
                <x-boton-enlace :href="route('medicos.index')">Volver</x-boton-enlace>
                @permiso('MEDICO_GESTIONAR')
                    <a href="{{ route('medicos.edit', $medico) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Editar
                    </a>
                @endpermiso
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Datos --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <h3 class="font-semibold text-gray-800">Datos del médico</h3>
                    <x-estado-badge :activo="$medico->estado" />
                </div>
                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div><dt class="text-gray-500">Registro profesional</dt><dd class="font-medium">{{ $medico->registro_profesional }}</dd></div>
                    <div><dt class="text-gray-500">Teléfono</dt><dd class="font-medium">{{ $medico->telefono }}</dd></div>
                    <div><dt class="text-gray-500">Correo</dt><dd class="font-medium">{{ $medico->correo }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Especialidades</dt>
                        <dd class="font-medium">
                            @foreach ($medico->especialidades as $esp)
                                {{ $esp->nombre }}
                                <span class="text-xs text-gray-400">(desde {{ \Carbon\Carbon::parse($esp->pivot->fecha_asignacion)->format('d/m/Y') }})</span>@if (! $loop->last), @endif
                            @endforeach
                        </dd>
                    </div>
                    <div><dt class="text-gray-500">Usuario</dt><dd class="font-medium">{{ $medico->user?->name ?? '—' }}</dd></div>
                </dl>
                @unless ($medico->estado)
                    <p class="mt-4 text-sm text-red-600">Médico inactivo: no puede recibir nuevas citas. Sus citas anteriores se conservan.</p>
                @endunless
            </div>

            {{-- Horarios semanales --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Horarios de atención</h3>
                    @if (Route::has('horarios.create'))
                        @permiso('HORARIO_GESTIONAR')
                            <a href="{{ route('horarios.create', ['medico_id' => $medico->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar horario</a>
                        @endpermiso
                    @endif
                </div>

                @if ($medico->horarios->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Sin horarios registrados.</p>
                @else
                    <table class="mt-3 min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="py-2">Día</th><th class="py-2">Horario</th><th class="py-2">Duración</th>
                                <th class="py-2">Consultorio</th><th class="py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($medico->horarios as $h)
                                <tr>
                                    <td class="py-2">{{ $h->diaSemana->nombre }}</td>
                                    <td class="py-2">{{ $h->rango }}</td>
                                    <td class="py-2">{{ $h->duracion_cita }} min</td>
                                    <td class="py-2">{{ $h->consultorio->numero }}</td>
                                    <td class="py-2"><x-estado-badge :activo="$h->estado" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Disponibilidad de un día (RF05: calcular los horarios disponibles) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('medicos.show', $medico) }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="fecha" value="Disponibilidad del día" />
                        <x-text-input id="fecha" name="fecha" type="date" class="block mt-1"
                                      :value="$fecha->toDateString()" min="{{ today()->toDateString() }}" />
                    </div>
                    <x-primary-button>Consultar</x-primary-button>
                </form>

                <p class="mt-3 text-sm text-gray-600">
                    {{ ucfirst($fecha->translatedFormat('l d/m/Y')) }}
                </p>

                @if (empty($bloques))
                    <p class="mt-2 text-sm text-gray-500">
                        No hay horarios de atención para esta fecha{{ $medico->estado ? '' : ' (médico inactivo)' }}.
                    </p>
                @else
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($bloques as $b)
                            <span class="px-3 py-1 rounded-md text-sm border
                                {{ $b['libre'] ? 'bg-green-50 border-green-200 text-green-800' : 'bg-gray-100 border-gray-200 text-gray-400 line-through' }}"
                                  title="Consultorio {{ $b['consultorio'] }}">
                                {{ $b['inicio'] }}–{{ $b['fin'] }}
                            </span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Verde: disponible · Gris: ocupado o ya pasó.</p>
                @endif
            </div>

            {{-- Próximas citas --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Próximas citas</h3>

                @if ($proximasCitas->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">No tiene citas próximas.</p>
                @else
                    <table class="mt-3 min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="py-2">Fecha</th><th class="py-2">Hora</th><th class="py-2">Código</th>
                                <th class="py-2">Paciente</th><th class="py-2">Especialidad</th><th class="py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($proximasCitas as $cita)
                                <tr>
                                    <td class="py-2">{{ $cita->fecha->format('d/m/Y') }}</td>
                                    <td class="py-2">{{ substr($cita->hora_inicio, 0, 5) }}–{{ substr($cita->hora_fin, 0, 5) }}</td>
                                    <td class="py-2 font-mono">{{ $cita->codigo }}</td>
                                    <td class="py-2">{{ $cita->paciente->nombres }} {{ $cita->paciente->apellidos }}</td>
                                    <td class="py-2">{{ $cita->especialidad->nombre }}</td>
                                    <td class="py-2">{{ $cita->estado->nombre }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>