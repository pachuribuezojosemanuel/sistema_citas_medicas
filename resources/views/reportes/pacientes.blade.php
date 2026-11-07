<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte: pacientes registrados</h2>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <form method="GET" action="{{ route('reportes.pacientes') }}"
                  class="no-imprimir bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <x-input-label for="desde" value="Registrados desde" />
                    <x-text-input id="desde" name="desde" type="date" class="block mt-1" :value="$desde->toDateString()" />
                </div>
                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" class="block mt-1" :value="$hasta->toDateString()" />
                </div>
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <x-select-input id="estado" name="estado" class="block mt-1">
                        <option value="">Todos</option>
                        <option value="1" @selected(request('estado') === '1')>Activos</option>
                        <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                    </x-select-input>
                </div>
                @include('reportes._acciones')
                <x-input-error :messages="$errors->get('hasta')" class="w-full" />
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('reportes._encabezado', [
                    'titulo'  => 'Pacientes registrados',
                    'periodo' => 'Del ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y'),
                ])

                <div class="flex flex-wrap gap-4 text-sm">
                    <span class="font-semibold text-gray-900">Total: {{ $pacientes->count() }}</span>
                    <span>Activos: {{ $pacientes->where('estado', true)->count() }}</span>
                    <span>Inactivos: {{ $pacientes->where('estado', false)->count() }}</span>
                    @foreach ($pacientes->countBy(fn ($p) => $p->sexo->nombre) as $sexo => $cantidad)
                        <span>{{ $sexo }}: {{ $cantidad }}</span>
                    @endforeach
                </div>

                <div class="overflow-x-auto">
                    <table class="mt-4 min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="px-3 py-2">Código</th><th class="px-3 py-2">CI</th><th class="px-3 py-2">Paciente</th>
                                <th class="px-3 py-2">Edad</th><th class="px-3 py-2">Sexo</th><th class="px-3 py-2">Teléfono</th>
                                <th class="px-3 py-2">Registrado</th><th class="px-3 py-2 text-right">Citas</th><th class="px-3 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($pacientes as $p)
                                <tr>
                                    <td class="px-3 py-2 font-mono">{{ $p->codigo }}</td>
                                    <td class="px-3 py-2">{{ $p->ci }}</td>
                                    <td class="px-3 py-2">{{ $p->nombre_completo }}</td>
                                    <td class="px-3 py-2">{{ $p->edad }}</td>
                                    <td class="px-3 py-2">{{ $p->sexo->nombre }}</td>
                                    <td class="px-3 py-2">{{ $p->telefono }}</td>
                                    <td class="px-3 py-2">{{ $p->created_at->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 text-right">{{ $p->citas_count }}</td>
                                    <td class="px-3 py-2">{{ $p->estado ? 'Activo' : 'Inactivo' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-3 py-6 text-center text-gray-500">No hay pacientes registrados en el período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/reportes.js') }}"></script>
</x-app-layout>