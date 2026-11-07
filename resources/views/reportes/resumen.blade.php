@php
    $tasa = ($general->atendidas + $general->inasistencias) > 0
        ? round(100 * $general->inasistencias / ($general->atendidas + $general->inasistencias), 1)
        : 0;
    $tarjetas = [
        ['Total de citas',  $general->total,         'text-gray-900'],
        ['Pendientes',      $general->pendientes,    'text-blue-700'],
        ['Atendidas',       $general->atendidas,     'text-green-700'],
        ['Canceladas',      $general->canceladas,    'text-red-700'],
        ['Inasistencias',   $general->inasistencias, 'text-amber-700'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte: resumen del período</h2>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <form method="GET" action="{{ route('reportes.resumen') }}"
                  class="no-imprimir bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" class="block mt-1" :value="$desde->toDateString()" />
                </div>
                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" class="block mt-1" :value="$hasta->toDateString()" />
                </div>
                @include('reportes._acciones')
                <x-input-error :messages="$errors->get('hasta')" class="w-full" />
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('reportes._encabezado', [
                    'titulo'  => 'Resumen del período',
                    'periodo' => 'Del ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y'),
                ])

                {{-- Totales generales --}}
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                    @foreach ($tarjetas as $t)
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-2xl font-semibold {{ $t[2] }}">{{ $t[1] }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $t[0] }}</div>
                        </div>
                    @endforeach
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="text-2xl font-semibold text-gray-900">{{ $tasa }}%</div>
                        <div class="text-xs text-gray-500 mt-1">Tasa de inasistencia</div>
                    </div>
                </div>

                {{-- Por médico y por especialidad --}}
                @foreach (['Por médico' => $porMedico, 'Por especialidad' => $porEspecialidad] as $titulo => $filas)
                    <h3 class="mt-8 font-semibold text-gray-800">{{ $titulo }}</h3>
                    <div class="overflow-x-auto evitar-corte">
                        <table class="mt-3 min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                    <th class="px-3 py-2">{{ $titulo === 'Por médico' ? 'Médico' : 'Especialidad' }}</th>
                                    <th class="px-3 py-2 text-right">Total</th>
                                    <th class="px-3 py-2 text-right">Pendientes</th>
                                    <th class="px-3 py-2 text-right">Atendidas</th>
                                    <th class="px-3 py-2 text-right">Canceladas</th>
                                    <th class="px-3 py-2 text-right">Inasistencias</th>
                                    <th class="px-3 py-2 w-1/4">Proporción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($filas as $f)
                                    <tr>
                                        <td class="px-3 py-2 font-medium">{{ $f->nombre }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">{{ $f->total }}</td>
                                        <td class="px-3 py-2 text-right">{{ $f->pendientes }}</td>
                                        <td class="px-3 py-2 text-right">{{ $f->atendidas }}</td>
                                        <td class="px-3 py-2 text-right">{{ $f->canceladas }}</td>
                                        <td class="px-3 py-2 text-right">{{ $f->inasistencias }}</td>
                                        <td class="px-3 py-2">
                                            <div class="h-2 bg-gray-100 rounded">
                                                <div class="h-2 bg-indigo-500 rounded"
                                                     style="width: {{ $general->total ? round(100 * $f->total / $general->total) : 0 }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">Sin citas en el período.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="{{ asset('js/reportes.js') }}"></script>
</x-app-layout>