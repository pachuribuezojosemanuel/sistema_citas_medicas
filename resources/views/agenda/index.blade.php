@php
    $ruta     = $propia ? 'mi-agenda.index' : 'agenda.index';
    $anterior = request()->fullUrlWithQuery(['fecha' => $fecha->copy()->subDay()->toDateString()]);
    $siguiente = request()->fullUrlWithQuery(['fecha' => $fecha->copy()->addDay()->toDateString()]);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $propia ? 'Mi agenda' : 'Agenda médica' }}</h2>
            @if ($medico)
                <button type="button" data-imprimir class="no-imprimir inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Imprimir
                </button>
            @endif
        </div>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros: médico, fecha y estado (RF11) --}}
            <form method="GET" action="{{ route($ruta) }}"
                  class="no-imprimir bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                @unless ($propia)
                    <div class="sm:col-span-2">
                        <x-input-label for="medico_id" value="Médico" />
                        <x-select-input id="medico_id" name="medico_id" class="block mt-1 w-full">
                            <option value="">— Seleccione un médico —</option>
                            @foreach ($medicos as $m)
                                <option value="{{ $m->id }}" @selected($medico?->id === $m->id)>
                                    {{ $m->apellidos }}, {{ $m->nombres }} {{ $m->estado ? '' : '(inactivo)' }}
                                </option>
                            @endforeach
                        </x-select-input>
                    </div>
                @endunless
                <div>
                    <x-input-label for="fecha" value="Fecha" />
                    <x-text-input id="fecha" name="fecha" type="date" class="block mt-1 w-full" :value="$fecha->toDateString()" />
                </div>
                <div>
                    <x-input-label for="estado_cita_id" value="Estado" />
                    <x-select-input id="estado_cita_id" name="estado_cita_id" class="block mt-1 w-full">
                        <option value="">Todos</option>
                        @foreach ($estados as $e)
                            <option value="{{ $e->id }}" @selected($estadoId === $e->id)>{{ $e->nombre }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Ver</x-primary-button>
                    <x-boton-enlace :href="$anterior" title="Día anterior">‹</x-boton-enlace>
                    <x-boton-enlace :href="$siguiente" title="Día siguiente">›</x-boton-enlace>
                </div>
            </form>

            @if (! $medico)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-500">Seleccione un médico para ver su agenda.</div>
            @else
                {{-- Encabezado --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="solo-imprimir text-sm text-gray-500 mb-2">Clínica Vida Salud · Agenda médica</div>
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $medico->nombre_completo }}</h3>
                            <p class="text-gray-600">{{ ucfirst($fecha->translatedFormat('l d \d\e F \d\e Y')) }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-sm">
                            @forelse ($resumen as $nombre => $total)
                                <span class="flex items-center gap-1"><x-estado-cita :estado="$nombre" /> {{ $total }}</span>
                            @empty
                                <span class="text-gray-500">Sin citas</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Citas del día --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="px-4 py-3">Hora</th>
                                <th class="px-4 py-3">Código</th>
                                <th class="px-4 py-3">Paciente</th>
                                <th class="px-4 py-3">Especialidad</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3">Motivo</th>
                                <th class="px-4 py-3 text-right no-imprimir">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($citas as $c)
                                <tr>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $c->horario }}</td>
                                    <td class="px-4 py-3 font-mono">{{ $c->codigo }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $c->paciente->nombre_completo }}</div>
                                        <div class="text-xs text-gray-500">CI {{ $c->paciente->ci }} · {{ $c->paciente->edad }} años</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $c->especialidad->nombre }}</td>
                                    <td class="px-4 py-3"><x-estado-cita :estado="$c->estado->nombre" /></td>
                                    <td class="px-4 py-3 text-gray-600">{{ $c->motivo ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right no-imprimir">
                                        <a href="{{ route('citas.show', $c) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Abrir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">No hay citas para esta fecha{{ $estadoId ? ' con ese estado' : '' }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Horarios libres --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 no-imprimir">
                    <h3 class="font-semibold text-gray-800">Horarios libres</h3>
                    @if (empty($libres))
                        <p class="mt-2 text-sm text-gray-500">No hay horarios libres para esta fecha.</p>
                    @else
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($libres as $b)
                                <span class="px-3 py-1 rounded-md text-sm border bg-green-50 border-green-200 text-green-800">{{ $b['inicio'] }}–{{ $b['fin'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script src="{{ asset('js/reportes.js') }}"></script>
</x-app-layout>