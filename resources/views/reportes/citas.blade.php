<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte: listado de citas</h2>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <form method="GET" action="{{ route('reportes.citas') }}"
                  class="no-imprimir bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" class="block mt-1 w-full" :value="$desde->toDateString()" />
                </div>
                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" class="block mt-1 w-full" :value="$hasta->toDateString()" />
                </div>
                <div>
                    <x-input-label for="medico_id" value="Médico" />
                    <x-select-input id="medico_id" name="medico_id" class="block mt-1 w-full">
                        <option value="">Todos</option>
                        @foreach ($medicos as $m)
                            <option value="{{ $m->id }}" @selected(request('medico_id') == $m->id)>{{ $m->apellidos }}, {{ $m->nombres }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div>
                    <x-input-label for="especialidad_id" value="Especialidad" />
                    <x-select-input id="especialidad_id" name="especialidad_id" class="block mt-1 w-full">
                        <option value="">Todas</option>
                        @foreach ($especialidades as $e)
                            <option value="{{ $e->id }}" @selected(request('especialidad_id') == $e->id)>{{ $e->nombre }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div>
                    <x-input-label for="estado_cita_id" value="Estado" />
                    <x-select-input id="estado_cita_id" name="estado_cita_id" class="block mt-1 w-full">
                        <option value="">Todos</option>
                        @foreach ($estados as $est)
                            <option value="{{ $est->id }}" @selected(request('estado_cita_id') == $est->id)>{{ $est->nombre }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                <div class="lg:col-span-1 sm:col-span-3">
                    @include('reportes._acciones')
                </div>
                <div class="sm:col-span-3 lg:col-span-6"><x-input-error :messages="$errors->get('hasta')" /></div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('reportes._encabezado', [
                    'titulo'  => 'Listado de citas',
                    'periodo' => $desde->eq($hasta) ? 'Fecha: ' . $desde->format('d/m/Y') : 'Del ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y'),
                ])

                {{-- Totales --}}
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <span class="font-semibold text-gray-900">Total: {{ $citas->count() }}</span>
                    @foreach ($totales as $nombre => $cantidad)
                        <span class="flex items-center gap-1"><x-estado-cita :estado="$nombre" /> {{ $cantidad }}</span>
                    @endforeach
                </div>

                <div class="overflow-x-auto">
                    <table class="mt-4 min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="px-3 py-2">Fecha</th><th class="px-3 py-2">Hora</th><th class="px-3 py-2">Código</th>
                                <th class="px-3 py-2">Paciente</th><th class="px-3 py-2">Médico</th>
                                <th class="px-3 py-2">Especialidad</th><th class="px-3 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($citas as $c)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $c->fecha->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $c->horario }}</td>
                                    <td class="px-3 py-2 font-mono">{{ $c->codigo }}</td>
                                    <td class="px-3 py-2">{{ $c->paciente->nombre_completo }}</td>
                                    <td class="px-3 py-2">{{ $c->medico->nombre_completo }}</td>
                                    <td class="px-3 py-2">{{ $c->especialidad->nombre }}</td>
                                    <td class="px-3 py-2">{{ $c->estado->nombre }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">No hay citas para los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/reportes.js') }}"></script>
</x-app-layout>