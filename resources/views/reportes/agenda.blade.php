<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte: agenda diaria</h2>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <form method="GET" action="{{ route('reportes.agenda') }}"
                  class="no-imprimir bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <x-input-label for="fecha" value="Fecha" />
                    <x-text-input id="fecha" name="fecha" type="date" class="block mt-1" :value="$fecha->toDateString()" />
                </div>
                <div>
                    <x-input-label for="medico_id" value="Médico" />
                    <x-select-input id="medico_id" name="medico_id" class="block mt-1">
                        <option value="">Todos los médicos</option>
                        @foreach ($medicos as $m)
                            <option value="{{ $m->id }}" @selected(request('medico_id') == $m->id)>{{ $m->apellidos }}, {{ $m->nombres }}</option>
                        @endforeach
                    </x-select-input>
                </div>
                @include('reportes._acciones')
            </form>

            <p class="no-imprimir text-sm text-gray-600">
                {{ ucfirst($fecha->translatedFormat('l d \d\e F \d\e Y')) }} · {{ $total }} citas · {{ $porMedico->count() }} médicos
            </p>

            @forelse ($porMedico as $citas)
                @php
                    $medico = $citas->first()->medico;
                @endphp
                <div class="bg-white shadow-sm sm:rounded-lg p-6 {{ $loop->first ? '' : 'salto-pagina' }}">
                    @include('reportes._encabezado', [
                        'titulo'  => 'Agenda diaria — ' . $medico->nombre_completo,
                        'periodo' => ucfirst($fecha->translatedFormat('l d/m/Y')),
                    ])

                    <div class="flex flex-wrap items-baseline justify-between gap-2 no-imprimir">
                        <h3 class="font-semibold text-gray-900">{{ $medico->nombre_completo }}</h3>
                        <span class="text-sm text-gray-600">{{ $citas->count() }} citas</span>
                    </div>

                    <table class="mt-3 min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                <th class="px-3 py-2">Hora</th><th class="px-3 py-2">Código</th><th class="px-3 py-2">Paciente</th>
                                <th class="px-3 py-2">Especialidad</th><th class="px-3 py-2">Estado</th><th class="px-3 py-2">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($citas as $c)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap font-medium">{{ $c->horario }}</td>
                                    <td class="px-3 py-2 font-mono">{{ $c->codigo }}</td>
                                    <td class="px-3 py-2">{{ $c->paciente->nombre_completo }} <span class="text-xs text-gray-500">(CI {{ $c->paciente->ci }})</span></td>
                                    <td class="px-3 py-2">{{ $c->especialidad->nombre }}</td>
                                    <td class="px-3 py-2">{{ $c->estado->nombre }}</td>
                                    <td class="px-3 py-2">{{ $c->motivo ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-500">No hay citas para esta fecha.</div>
            @endforelse
        </div>
    </div>

    <script src="{{ asset('js/reportes.js') }}"></script>
</x-app-layout>