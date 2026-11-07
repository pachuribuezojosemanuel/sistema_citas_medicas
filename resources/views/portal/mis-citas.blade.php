<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis citas</h2>
            <a href="{{ route('mis-citas.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                + Reservar cita
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Próximas --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Próximas citas</h3>

                @forelse ($proximas as $c)
                    <div class="mt-4 rounded-lg border border-gray-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <div class="font-semibold text-gray-900">{{ ucfirst($c->fecha->translatedFormat('l d/m/Y')) }} · {{ $c->horario }}</div>
                                <div class="text-sm text-gray-600">{{ $c->medico->nombre_completo }} · {{ $c->especialidad->nombre }} · {{ $c->modalidad }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-1">{{ $c->codigo }}</div>
                            </div>
                            <x-estado-cita :estado="$c->estado->nombre" />
                        </div>

                        {{-- Cancelar (sin JavaScript: se despliega al hacer clic) --}}
                        <details class="mt-3">
                            <summary class="text-sm text-red-600 cursor-pointer">Cancelar esta cita</summary>
                            <form method="POST" action="{{ route('mis-citas.cancelar', $c) }}" class="mt-2"
                                  data-validar novalidate data-confirmar="¿Seguro que desea cancelar la cita {{ $c->codigo }}?">
                                @csrf
                                @method('PATCH')
                                <x-text-input name="motivo" class="block w-full text-sm" maxlength="200"
                                              placeholder="Motivo de la cancelación" data-reglas="requerido|min:5|max:200" />
                                <button class="mt-2 px-4 py-2 rounded-md bg-red-600 text-white text-xs font-semibold uppercase tracking-widest hover:bg-red-500">
                                    Confirmar cancelación
                                </button>
                            </form>
                        </details>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-gray-500">No tiene citas próximas.</p>
                @endforelse

                <p class="mt-4 text-xs text-gray-500">Para reprogramar una cita, comuníquese con recepción.</p>
            </div>

            {{-- Historial (RF12 desde el portal) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Historial</h3>

                @if ($historial->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">Todavía no tiene citas anteriores.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="mt-3 min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                    <th class="py-2 pe-4">Fecha</th><th class="py-2 pe-4">Hora</th><th class="py-2 pe-4">Médico</th>
                                    <th class="py-2 pe-4">Especialidad</th><th class="py-2">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($historial as $c)
                                    <tr>
                                        <td class="py-2 pe-4 whitespace-nowrap">{{ $c->fecha->format('d/m/Y') }}</td>
                                        <td class="py-2 pe-4 whitespace-nowrap">{{ $c->horario }}</td>
                                        <td class="py-2 pe-4">{{ $c->medico->nombre_completo }}</td>
                                        <td class="py-2 pe-4">{{ $c->especialidad->nombre }}</td>
                                        <td class="py-2">
                                            <x-estado-cita :estado="$c->estado->nombre" />
                                            @if ($c->cancelacion)
                                                <span class="block text-xs text-gray-500">{{ $c->cancelacion->motivo }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>