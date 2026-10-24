<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Horarios de atención</h2>
            @permiso('HORARIO_GESTIONAR')
                <a href="{{ route('horarios.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Nuevo horario
                </a>
            @endpermiso
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('horarios.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <x-select-input name="medico_id">
                    <option value="">Todos los médicos</option>
                    @foreach ($medicos as $m)
                        <option value="{{ $m->id }}" @selected(request('medico_id') == $m->id)>{{ $m->apellidos }}, {{ $m->nombres }}</option>
                    @endforeach
                </x-select-input>
                <x-select-input name="dia_semana_id">
                    <option value="">Todos los días</option>
                    @foreach ($dias as $d)
                        <option value="{{ $d->id }}" @selected(request('dia_semana_id') == $d->id)>{{ $d->nombre }}</option>
                    @endforeach
                </x-select-input>
                <x-select-input name="estado">
                    <option value="">Todos los estados</option>
                    <option value="1" @selected(request('estado') === '1')>Activos</option>
                    <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                </x-select-input>
                <div class="flex gap-2">
                    <x-primary-button>Filtrar</x-primary-button>
                    <x-boton-enlace :href="route('horarios.index')">Limpiar</x-boton-enlace>
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Médico</th>
                            <th class="px-4 py-3">Día</th>
                            <th class="px-4 py-3">Horario</th>
                            <th class="px-4 py-3">Duración</th>
                            <th class="px-4 py-3 text-center">Citas/día</th>
                            <th class="px-4 py-3">Consultorio</th>
                            <th class="px-4 py-3">Estado</th>
                            @permiso('HORARIO_GESTIONAR')
                                <th class="px-4 py-3 text-right">Acciones</th>
                            @endpermiso
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($horarios as $h)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $h->medico->nombre_completo }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $h->diaSemana->nombre }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $h->rango }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $h->duracion_cita }} min</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $h->cantidad_bloques }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $h->consultorio->numero }}</td>
                                <td class="px-4 py-3"><x-estado-badge :activo="$h->estado" /></td>
                                @permiso('HORARIO_GESTIONAR')
                                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                        <a href="{{ route('horarios.edit', $h) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>

                                        <form method="POST" action="{{ route('horarios.estado', $h) }}" class="inline"
                                              data-confirmar="¿{{ $h->estado ? 'Desactivar' : 'Activar' }} el horario del {{ $h->diaSemana->nombre }} {{ $h->rango }}?">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="font-medium {{ $h->estado ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                                {{ $h->estado ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </td>
                                @endpermiso
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">No se encontraron horarios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $horarios->links() }}
        </div>
    </div>
</x-app-layout>