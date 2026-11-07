@php
    $usuario        = Auth::user();
    $estado         = $cita->estado->nombre;
    $modificable    = $cita->esModificable();
    $yaEmpezo       = ! $cita->inicio()->isFuture();
    $puedeAsistencia = $usuario->tienePermiso('CITA_ASISTENCIA');
    $puedeAtender    = $puedeAsistencia || $usuario->tienePermiso('CITA_ATENDER');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-3">
                Cita {{ $cita->codigo }}
                <x-estado-cita :estado="$estado" />
            </h2>
            <x-boton-enlace :href="url()->previous() === url()->current() ? route('citas.index') : url()->previous()">Volver</x-boton-enlace>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Datos de la cita --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                    <h3 class="font-semibold text-gray-800">Datos de la cita</h3>
                    <dl class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div><dt class="text-gray-500">Fecha</dt><dd class="font-medium">{{ ucfirst($cita->fecha->translatedFormat('l d/m/Y')) }}</dd></div>
                        <div><dt class="text-gray-500">Horario</dt><dd class="font-medium">{{ $cita->horario }}</dd></div>
                        <div><dt class="text-gray-500">Modalidad</dt><dd class="font-medium">{{ $cita->modalidad }}</dd></div>
                        <div>
                            <dt class="text-gray-500">Paciente</dt>
                            <dd class="font-medium">
                                @if (Route::has('pacientes.show') && $usuario->tienePermiso('PACIENTE_VER'))
                                    <a href="{{ route('pacientes.show', $cita->paciente) }}" class="text-indigo-600 hover:underline">{{ $cita->paciente->nombre_completo }}</a>
                                @else
                                    {{ $cita->paciente->nombre_completo }}
                                @endif
                                <span class="block text-xs text-gray-500">CI {{ $cita->paciente->ci }} · {{ $cita->paciente->edad }} años · Tel. {{ $cita->paciente->telefono }}</span>
                            </dd>
                        </div>
                        <div><dt class="text-gray-500">Médico</dt><dd class="font-medium">{{ $cita->medico->nombre_completo }}</dd></div>
                        <div><dt class="text-gray-500">Especialidad</dt><dd class="font-medium">{{ $cita->especialidad->nombre }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Motivo</dt><dd class="font-medium">{{ $cita->motivo ?? '—' }}</dd></div>
                        <div>
                            <dt class="text-gray-500">Registrada</dt>
                            <dd class="font-medium">{{ $cita->created_at->format('d/m/Y H:i') }}
                                <span class="block text-xs text-gray-500">por {{ $cita->user->name }}</span>
                            </dd>
                        </div>
                    </dl>

                    @if ($cita->paciente->alergias->isNotEmpty())
                        <p class="mt-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
                            <strong>Alergias:</strong> {{ $cita->paciente->alergias->pluck('nombre')->join(', ') }}
                        </p>
                    @endif
                </div>

                {{-- Acciones según estado y permisos --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Acciones</h3>

                    @if (! $modificable)
                        <p class="text-sm text-gray-600">
                            La cita está en estado <strong>{{ $estado }}</strong>, que es un estado final. No admite más cambios.
                        </p>
                        @permiso('CITA_REGISTRAR')
                            <a href="{{ route('citas.create', ['paciente_id' => $cita->paciente_id]) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Registrar nueva cita
                            </a>
                        @endpermiso
                    @else
                        {{-- RF10: Confirmar asistencia --}}
                        @if ($estado === \App\Models\EstadoCita::PROGRAMADA && $puedeAsistencia)
                            <form method="POST" action="{{ route('citas.estado', $cita) }}" data-confirmar="¿Confirmar la asistencia del paciente?">
                                @csrf @method('PATCH')
                                <input type="hidden" name="estado" value="{{ \App\Models\EstadoCita::CONFIRMADA }}">
                                <input type="hidden" name="observacion" value="Asistencia confirmada">
                                <button class="w-full px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">Confirmar asistencia</button>
                            </form>
                        @endif

                        {{-- RF10: Atendida / Inasistencia (solo cuando la cita ya empezó) --}}
                        @if ($yaEmpezo)
                            @if ($puedeAtender)
                                <form method="POST" action="{{ route('citas.estado', $cita) }}" data-confirmar="¿Marcar la cita como atendida?">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="estado" value="{{ \App\Models\EstadoCita::ATENDIDA }}">
                                    <button class="w-full px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-500">Marcar como atendida</button>
                                </form>
                            @endif
                            @if ($puedeAsistencia)
                                <form method="POST" action="{{ route('citas.estado', $cita) }}" data-confirmar="¿Registrar que el paciente no se presentó?">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="estado" value="{{ \App\Models\EstadoCita::INASISTENCIA }}">
                                    <button class="w-full px-4 py-2 rounded-md bg-amber-500 text-white text-sm font-semibold hover:bg-amber-400">Registrar inasistencia</button>
                                </form>
                            @endif
                        @elseif ($puedeAtender)
                            <p class="text-xs text-gray-500">"Atendida" e "Inasistencia" se habilitan a partir de la hora de la cita.</p>
                        @endif

                        {{-- RF08: Reprogramar --}}
                        @permiso('CITA_REPROGRAMAR')
                            <a href="{{ route('citas.reprogramar', $cita) }}"
                               class="block text-center w-full px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                                Reprogramar
                            </a>
                        @endpermiso

                        {{-- RF09: Cancelar con motivo --}}
                        @permiso('CITA_CANCELAR')
                            <form method="POST" action="{{ route('citas.cancelar', $cita) }}" class="border-t pt-4"
                                  data-validar novalidate data-confirmar="¿Cancelar la cita {{ $cita->codigo }}? Esta acción no se puede deshacer.">
                                @csrf @method('PATCH')
                                <x-input-label for="motivo" value="Motivo de la cancelación *" />
                                <textarea id="motivo" name="motivo" rows="2" maxlength="200" data-reglas="requerido|min:5|max:200"
                                          class="block mt-1 w-full text-sm border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">{{ old('motivo') }}</textarea>
                                <x-input-error :messages="$errors->get('motivo')" class="mt-1" />
                                <button class="mt-2 w-full px-4 py-2 rounded-md bg-red-600 text-white text-sm font-semibold hover:bg-red-500">Cancelar cita</button>
                            </form>
                        @endpermiso
                    @endif
                </div>
            </div>

            {{-- Cancelación --}}
            @if ($cita->cancelacion)
                <div class="bg-red-50 border border-red-200 sm:rounded-lg p-6 text-sm">
                    <h3 class="font-semibold text-red-800">Cancelación</h3>
                    <p class="mt-2 text-red-800">{{ $cita->cancelacion->motivo }}</p>
                    <p class="mt-1 text-red-600 text-xs">
                        {{ $cita->cancelacion->fecha_cancelacion->format('d/m/Y H:i') }} · por {{ $cita->cancelacion->user->name }}
                    </p>
                </div>
            @endif

            {{-- Historial de estados (RF10) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Historial de estados</h3>
                <table class="mt-3 min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="py-2 pe-4">Fecha</th><th class="py-2 pe-4">De</th><th class="py-2 pe-4">A</th>
                            <th class="py-2 pe-4">Usuario</th><th class="py-2">Observación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($cita->historialEstados as $h)
                            <tr>
                                <td class="py-2 pe-4 whitespace-nowrap">{{ $h->fecha_cambio->format('d/m/Y H:i') }}</td>
                                <td class="py-2 pe-4">{{ $h->estadoAnterior?->nombre ?? '—' }}</td>
                                <td class="py-2 pe-4"><x-estado-cita :estado="$h->estadoNuevo->nombre" /></td>
                                <td class="py-2 pe-4">{{ $h->user->name }}</td>
                                <td class="py-2 text-gray-600">{{ $h->observacion }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Reprogramaciones (RF08) --}}
            @if ($cita->reprogramaciones->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800">Reprogramaciones</h3>
                    <table class="mt-3 min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="py-2 pe-4">Registrada</th><th class="py-2 pe-4">Antes</th><th class="py-2 pe-4">Después</th>
                                <th class="py-2 pe-4">Usuario</th><th class="py-2">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($cita->reprogramaciones as $r)
                                <tr>
                                    <td class="py-2 pe-4 whitespace-nowrap">{{ $r->fecha_registro->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 pe-4 whitespace-nowrap">{{ $r->fecha_anterior->format('d/m/Y') }} {{ substr($r->hora_anterior, 0, 5) }}</td>
                                    <td class="py-2 pe-4 whitespace-nowrap">{{ $r->fecha_nueva->format('d/m/Y') }} {{ substr($r->hora_nueva, 0, 5) }}</td>
                                    <td class="py-2 pe-4">{{ $r->user->name }}</td>
                                    <td class="py-2 text-gray-600">{{ $r->motivo }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>