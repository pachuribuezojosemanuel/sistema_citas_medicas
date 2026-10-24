<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuarios y personal</h2>
            <a href="{{ route('usuarios.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                + Nuevo usuario
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('usuarios.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <x-text-input name="buscar" :value="$buscar" placeholder="Usuario, correo, nombre o CI"
                              class="sm:col-span-2" maxlength="100" />

                <x-select-input name="rol_id">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}" @selected(request('rol_id') == $rol->id)>{{ $rol->nombre }}</option>
                    @endforeach
                </x-select-input>

                <div class="flex gap-2">
                    <x-select-input name="estado" class="flex-1">
                        <option value="">Todos</option>
                        <option value="1" @selected(request('estado') === '1')>Activos</option>
                        <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                    </x-select-input>
                    <x-primary-button>Filtrar</x-primary-button>
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Último acceso</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($usuarios as $u)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $u->name }}</div>
                                    @if ($u->nombre_mostrar)
                                        <div class="text-xs text-gray-500">
                                            {{ $u->nombre_mostrar }}
                                            @if ($u->personal) · {{ $u->personal->cargo }} @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-amber-600">Sin datos personales</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $u->email }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $u->rol->nombre }}</td>
                                <td class="px-4 py-3"><x-estado-badge :activo="$u->estado" /></td>
                                <td class="px-4 py-3 text-gray-600">{{ $u->ultimo_acceso?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">
                                    <a href="{{ route('usuarios.edit', $u) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>

                                    @if ($u->id !== Auth::id())
                                        <form method="POST" action="{{ route('usuarios.estado', $u) }}" class="inline"
                                              data-confirmar="¿{{ $u->estado ? 'Desactivar' : 'Activar' }} al usuario {{ $u->name }}?">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="font-medium {{ $u->estado ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                                {{ $u->estado ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No se encontraron usuarios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $usuarios->links() }}
        </div>
    </div>
</x-app-layout>1