<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Médicos</h2>
            @permiso('MEDICO_GESTIONAR')
                <a href="{{ route('medicos.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Nuevo médico
                </a>
            @endpermiso
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros: buscar por nombre o especialidad (RF03) --}}
            <form method="GET" action="{{ route('medicos.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-5 gap-3">
                <x-text-input name="buscar" :value="$buscar" placeholder="Nombre, código o registro"
                              class="sm:col-span-2" maxlength="60" />
                <x-select-input name="especialidad_id">
                    <option value="">Todas las especialidades</option>
                    @foreach ($especialidades as $esp)
                        <option value="{{ $esp->id }}" @selected(request('especialidad_id') == $esp->id)>{{ $esp->nombre }}</option>
                    @endforeach
                </x-select-input>
                <x-select-input name="estado">
                    <option value="">Todos los estados</option>
                    <option value="1" @selected(request('estado') === '1')>Activos</option>
                    <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                </x-select-input>
                <div class="flex gap-2">
                    <x-primary-button>Filtrar</x-primary-button>
                    <x-boton-enlace :href="route('medicos.index')">Limpiar</x-boton-enlace>
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Médico</th>
                            <th class="px-4 py-3">Especialidades</th>
                            <th class="px-4 py-3">Teléfono</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($medicos as $m)
                            <tr>
                                <td class="px-4 py-3 font-mono text-gray-700">{{ $m->codigo }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $m->nombre_completo }}</div>
                                    <div class="text-xs text-gray-500">Reg. {{ $m->registro_profesional }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $m->especialidades->pluck('nombre')->join(', ') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $m->telefono }}</td>
                                <td class="px-4 py-3"><x-estado-badge :activo="$m->estado" /></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                    <a href="{{ route('medicos.show', $m) }}" class="text-gray-700 hover:text-gray-900 font-medium">Agenda</a>

                                    @permiso('MEDICO_GESTIONAR')
                                        <a href="{{ route('medicos.edit', $m) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>

                                        <form method="POST" action="{{ route('medicos.estado', $m) }}" class="inline"
                                              data-confirmar="¿{{ $m->estado ? 'Desactivar' : 'Activar' }} al médico {{ $m->nombre_completo }}?">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="font-medium {{ $m->estado ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                                {{ $m->estado ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endpermiso
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No se encontraron médicos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $medicos->links() }}
        </div>
    </div>
</x-app-layout>