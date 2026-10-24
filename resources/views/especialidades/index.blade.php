<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Especialidades médicas</h2>
            <a href="{{ route('especialidades.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                + Nueva especialidad
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('especialidades.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <x-text-input name="buscar" :value="$buscar" placeholder="Buscar por nombre"
                              class="sm:col-span-2" maxlength="60" />
                <x-select-input name="estado">
                    <option value="">Todos los estados</option>
                    <option value="1" @selected(request('estado') === '1')>Activas</option>
                    <option value="0" @selected(request('estado') === '0')>Inactivas</option>
                </x-select-input>
                <div class="flex gap-2">
                    <x-primary-button>Filtrar</x-primary-button>
                    <x-boton-enlace :href="route('especialidades.index')">Limpiar</x-boton-enlace>
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3 text-center">Médicos</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($especialidades as $e)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $e->nombre }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $e->descripcion ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $e->medicos_count }}</td>
                                <td class="px-4 py-3"><x-estado-badge :activo="$e->estado" /></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                    <a href="{{ route('especialidades.edit', $e) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>

                                    <form method="POST" action="{{ route('especialidades.estado', $e) }}" class="inline"
                                          data-confirmar="¿{{ $e->estado ? 'Desactivar' : 'Activar' }} la especialidad {{ $e->nombre }}?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="font-medium {{ $e->estado ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                            {{ $e->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No se encontraron especialidades.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $especialidades->links() }}
        </div>
    </div>
</x-app-layout>