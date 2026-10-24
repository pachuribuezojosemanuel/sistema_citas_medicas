<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $paciente->nombre_completo }}
                <span class="text-sm font-normal text-gray-500">({{ $paciente->codigo }})</span>
            </h2>
            <div class="flex gap-2">
                <x-boton-enlace :href="route('pacientes.index')">Volver</x-boton-enlace>
                @if (Route::has('citas.create') && $paciente->estado)
                    @permiso('CITA_REGISTRAR')
                        <a href="{{ route('citas.create', ['paciente_id' => $paciente->id]) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Nueva cita
                        </a>
                    @endpermiso
                @endif
                @permiso('PACIENTE_GESTIONAR')
                    <a href="{{ route('pacientes.edit', $paciente) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Editar
                    </a>
                @endpermiso
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Datos personales y contacto --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                    <div class="flex items-start justify-between">
                        <h3 class="font-semibold text-gray-800">Datos personales</h3>
                        <x-estado-badge :activo="$paciente->estado" />
                    </div>
                    <dl class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div><dt class="text-gray-500">CI</dt><dd class="font-medium">{{ $paciente->ci }}</dd></div>
                        <div><dt class="text-gray-500">Fecha de nacimiento</dt><dd class="font-medium">{{ $paciente->fecha_nacimiento->format('d/m/Y') }} ({{ $paciente->edad }} años)</dd></div>
                        <div><dt class="text-gray-500">Sexo</dt><dd class="font-medium">{{ $paciente->sexo->nombre }}</dd></div>
                        <div><dt class="text-gray-500">Teléfono</dt><dd class="font-medium">{{ $paciente->telefono }}</dd></div>
                        <div><dt class="text-gray-500">Correo</dt><dd class="font-medium">{{ $paciente->correo ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Dirección</dt><dd class="font-medium">{{ $paciente->direccion ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Registrado</dt><dd class="font-medium">{{ $paciente->created_at->format('d/m/Y') }}</dd></div>
                        <div><dt class="text-gray-500">Cuenta</dt><dd class="font-medium">{{ $paciente->user?->name ?? 'Sin cuenta' }}</dd></div>
                    </dl>
                    @unless ($paciente->estado)
                        <p class="mt-4 text-sm text-red-600">Paciente inactivo. Su historial se conserva.</p>
                    @endunless
                </div>

                {{-- Contactos de emergencia --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800">Contactos de emergencia</h3>
                    @forelse ($paciente->contactosEmergencia as $c)
                        <div class="mt-3 text-sm">
                            <div class="font-medium">{{ $c->nombre_completo }}</div>
                            <div class="text-gray-500">{{ $c->parentesco }} · {{ $c->telefono }}</div>
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-gray-500">Sin contactos registrados.</p>
                    @endforelse
                </div>
            </div>

            {{-- Información médica --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Información médica</h3>
                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Tipo de sangre</dt>
                        <dd class="font-medium">{{ $paciente->tipoSangre?->nombre ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Alergias</dt>
                        <dd>
                            @forelse ($paciente->alergias as $a)
                                <div class="font-medium text-red-700">{{ $a->nombre }}
                                    @if ($a->pivot->observacion)<span class="font-normal text-gray-500">— {{ $a->pivot->observacion }}</span>@endif
                                </div>
                            @empty
                                <span class="font-medium">Ninguna registrada</span>
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Enfermedades crónicas</dt>
                        <dd>
                            @forelse ($paciente->enfermedades as $e)
                                <div class="font-medium">{{ $e->nombre }}
                                    <span class="font-normal text-gray-500">
                                        @if ($e->pivot->fecha_diagnostico) (desde {{ \Carbon\Carbon::parse($e->pivot->fecha_diagnostico)->format('d/m/Y') }}) @endif
                                        @if ($e->pivot->observacion) — {{ $e->pivot->observacion }} @endif
                                    </span>
                                </div>
                            @empty
                                <span class="font-medium">Ninguna registrada</span>
                            @endforelse
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Medicamentos</dt>
                        <dd class="font-medium whitespace-pre-line">{{ $paciente->medicamentos ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <dt class="text-gray-500">Antecedentes</dt>
                        <dd class="font-medium whitespace-pre-line">{{ $paciente->antecedentes ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Historial de citas (RF02: consultar sus citas anteriores) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Historial de citas</h3>

                @if ($citas->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">El paciente no tiene citas registradas.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="mt-3 min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                    <th class="py-2 pe-4">Código</th>
                                    <th class="py-2 pe-4">Fecha</th>
                                    <th class="py-2 pe-4">Hora</th>
                                    <th class="py-2 pe-4">Médico</th>
                                    <th class="py-2 pe-4">Especialidad</th>
                                    <th class="py-2 pe-4">Estado</th>
                                    <th class="py-2">Registrada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($citas as $cita)
                                    <tr>
                                        <td class="py-2 pe-4 font-mono">{{ $cita->codigo }}</td>
                                        <td class="py-2 pe-4">{{ $cita->fecha->format('d/m/Y') }}</td>
                                        <td class="py-2 pe-4">{{ substr($cita->hora_inicio, 0, 5) }}–{{ substr($cita->hora_fin, 0, 5) }}</td>
                                        <td class="py-2 pe-4">{{ $cita->medico->nombre_completo }}</td>
                                        <td class="py-2 pe-4">{{ $cita->especialidad->nombre }}</td>
                                        <td class="py-2 pe-4">{{ $cita->estado->nombre }}</td>
                                        <td class="py-2">{{ $cita->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>