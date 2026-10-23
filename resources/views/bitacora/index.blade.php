<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bitácora del sistema</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('bitacora.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                <div>
                    <x-input-label for="user_id" value="Usuario" />
                    <x-select-input id="user_id" name="user_id" class="block mt-1 w-full">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </x-select-input>
                </div>

                <div>
                    <x-input-label for="accion" value="Acción" />
                    <x-select-input id="accion" name="accion" class="block mt-1 w-full">
                        <option value="">Todas</option>
                        @foreach ($acciones as $accion)
                            <option value="{{ $accion }}" @selected(request('accion') === $accion)>{{ $accion }}</option>
                        @endforeach
                    </x-select-input>
                </div>

                <div>
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" class="block mt-1 w-full" :value="request('desde')" />
                </div>

                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" class="block mt-1 w-full" :value="request('hasta')" />
                </div>

                <div class="flex gap-2">
                    <x-primary-button>Filtrar</x-primary-button>
                    <x-boton-enlace :href="route('bitacora.index')">Limpiar</x-boton-enlace>
                </div>

                <div class="sm:col-span-5">
                    <x-input-error :messages="$errors->get('hasta')" />
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-4 py-3">Fecha y hora</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Acción</th>
                            <th class="px-4 py-3">Módulo</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($registros as $r)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $r->fecha_hora->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $r->user->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
                                        {{ str_contains($r->accion, 'FALLIDO') || str_contains($r->accion, 'DENEGADO') ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $r->accion }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $r->modulo }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $r->descripcion }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $r->ip }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No hay registros para los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $registros->links() }}
        </div>
    </div>
</x-app-layout>