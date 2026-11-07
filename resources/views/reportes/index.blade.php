<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reportes</h2>
    </x-slot>

    @php
        $reportes = [
            ['ruta' => 'reportes.citas',     'titulo' => 'Listado de citas',     'texto' => 'Citas de una fecha o rango, filtradas por médico, especialidad y estado, con totales por estado.'],
            ['ruta' => 'reportes.resumen',   'titulo' => 'Resumen del período',  'texto' => 'Cantidad de citas atendidas, canceladas e inasistencias por médico, por especialidad y en total.'],
            ['ruta' => 'reportes.agenda',    'titulo' => 'Agenda diaria',        'texto' => 'Agenda del día de cada médico, lista para imprimir (un médico por página).'],
            ['ruta' => 'reportes.pacientes', 'titulo' => 'Pacientes registrados','texto' => 'Pacientes registrados en un período, con su cantidad de citas.'],
        ];
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($reportes as $r)
                <a href="{{ route($r['ruta']) }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-200">
                    <h3 class="font-semibold text-gray-900">{{ $r['titulo'] }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $r['texto'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>