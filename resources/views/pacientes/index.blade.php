<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pacientes</h2>
            @permiso('PACIENTE_GESTIONAR')
                <a href="{{ route('pacientes.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    + Nuevo paciente
                </a>
            @endpermiso
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Búsqueda por CI, nombre o apellido (RF02) --}}
            <form method="GET" action="{{ route('pacientes.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <x-text-input name="buscar" :value="$buscar" placeholder="Buscar por CI, nombre, apellido o código"
                              class="sm:col-span-2" maxlength="60" autofocus />
                <x-select-input name="estado">
                    <option value="">Todos los estados</option>
                    <option value="1" @selected(request('estado') === '1')>Activos</option>
                    <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                </x-select-input>
                <div class="flex gap-2">
                    <x-primary-button>Buscar</x-primary-button>
                    <x-boton-enlace :href="route('pacientes.index')">Limpiar</x-boton-enlace>
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">CI</th>
                            <th class="px-4 py-3">Paciente</th>
                            <th class="px-4 py-3">Edad</th>
                            <th class="px-4 py-3">Teléfono</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pacientes as $p)
                            <tr>
                                <td class="px-4 py-3 font-mono text-gray-700">{{ $p->codigo }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $p->ci }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $p->apellidos }}, {{ $p->nombres }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $p->edad }} años</td>
                                <td class="px-4 py-3 text-gray-600">{{ $p->telefono }}</td>
                                <td class="px-4 py-3"><x-estado-badge :activo="$p->estado" /></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                    <a href="{{ route('pacientes.show', $p) }}" class="text-gray-700 hover:text-gray-900 font-medium">Ver</a>

                                    @permiso('PACIENTE_GESTIONAR')
                                        <a href="{{ route('pacientes.edit', $p) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>

                                        <form method="POST" action="{{ route('pacientes.estado', $p) }}" class="inline"
                                              data-confirmar="¿{{ $p->estado ? 'Desactivar' : 'Activar' }} al paciente {{ $p->nombre_completo }}?">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="font-medium {{ $p->estado ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                                {{ $p->estado ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endpermiso
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">No se encontraron pacientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $pacientes->links() }}
        </div>
    </div>
</x-app-layout>