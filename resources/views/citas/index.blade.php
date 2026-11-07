<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Citas médicas</h2>
            @permiso('CITA_REGISTRAR')
                <a href="{{ route('citas.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Nueva cita
                </a>
            @endpermiso
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros: por fecha, médico, especialidad y estado --}}
            <form method="GET" action="{{ route('citas.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <x-input-label for="fecha" value="Fecha" />
                    <x-text-input id="fecha" name="fecha" type="date" class="block mt-1 w-full" :value="$fecha" />
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
                <div>
                    <x-input-label for="buscar" value="Paciente o código" />
                    <x-text-input id="buscar" name="buscar" class="block mt-1 w-full" :value="$buscar" placeholder="CI, nombre o CM-…" maxlength="60" />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>Filtrar</x-primary-button>
                    <x-boton-enlace :href="route('citas.index')">Hoy</x-boton-enlace>
                </div>
                <p class="sm:col-span-3 lg:col-span-6 text-xs text-gray-500">Deje la fecha vacía para ver citas de todas las fechas.</p>
            </form>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Hora</th>
                            <th class="px-4 py-3">Paciente</th>
                            <th class="px-4 py-3">Médico</th>
                            <th class="px-4 py-3">Especialidad</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($citas as $c)
                            <tr>
                                <td class="px-4 py-3 font-mono text-gray-700">{{ $c->codigo }}</td>
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $c->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $c->horario }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $c->paciente->nombre_completo }}</div>
                                    <div class="text-xs text-gray-500">CI {{ $c->paciente->ci }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $c->medico->nombre_completo }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $c->especialidad->nombre }}</td>
                                <td class="px-4 py-3"><x-estado-cita :estado="$c->estado->nombre" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('citas.show', $c) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">No hay citas para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $citas->links() }}
        </div>
    </div>
</x-app-layout>