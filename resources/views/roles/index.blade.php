<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Roles y permisos</h2>
            <a href="{{ route('roles.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                + Nuevo rol
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3 text-center">Usuarios</th>
                            <th class="px-4 py-3 text-center">Permisos</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($roles as $rol)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $rol->nombre }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $rol->descripcion ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $rol->users_count }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $rol->permisos_count }}</td>
                                <td class="px-4 py-3"><x-estado-badge :activo="$rol->estado" /></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                    <a href="{{ route('roles.edit', $rol) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>

                                    <form method="POST" action="{{ route('roles.estado', $rol) }}" class="inline"
                                          data-confirmar="¿{{ $rol->estado ? 'Desactivar' : 'Activar' }} el rol {{ $rol->nombre }}?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="font-medium {{ $rol->estado ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                            {{ $rol->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No hay roles registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>