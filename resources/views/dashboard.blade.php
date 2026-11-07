<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inicio</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Bienvenida --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    Bienvenido(a), {{ $usuario->nombre_mostrar ?? $usuario->name }}
                </h3>
                <p class="mt-1 text-gray-600">
                    {{ $usuario->rol->nombre }}
                    @if ($usuario->personal) · {{ $usuario->personal->cargo }} @endif
                    · {{ ucfirst(today()->translatedFormat('l d \d\e F \d\e Y')) }}
                </p>
            </div>

            {{-- Paciente: próxima cita --}}
            @isset($proximaCita)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800">Su próxima cita</h3>
                    @if ($proximaCita)
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                            <span class="font-mono">{{ $proximaCita->codigo }}</span>
                            <span class="font-medium">{{ ucfirst($proximaCita->fecha->translatedFormat('l d/m/Y')) }}, {{ $proximaCita->horario }}</span>
                            <span>{{ $proximaCita->medico->nombre_completo }} · {{ $proximaCita->especialidad->nombre }}</span>
                            <x-estado-cita :estado="$proximaCita->estado->nombre" />
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500">No tiene citas pendientes.</p>
                    @endif
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('mis-citas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">Reservar cita</a>
                        <x-boton-enlace :href="route('mis-citas.index')">Ver mis citas</x-boton-enlace>
                    </div>
                </div>
            @endisset

            {{-- Médico: citas de hoy --}}
            @isset($citasHoy)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Sus citas de hoy ({{ $citasHoy->count() }})</h3>
                        <x-boton-enlace :href="route('mi-agenda.index')">Ver mi agenda</x-boton-enlace>
                    </div>
                    @forelse ($citasHoy as $c)
                        <div class="mt-3 flex items-center gap-3 text-sm">
                            <span class="font-medium w-24">{{ $c->horario }}</span>
                            <a href="{{ route('citas.show', $c) }}" class="text-indigo-600 hover:underline">{{ $c->paciente->nombre_completo }}</a>
                            <x-estado-cita :estado="$c->estado->nombre" />
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-gray-500">No tiene citas para hoy.</p>
                    @endforelse
                </div>
            @endisset

            {{-- Personal: resumen del día --}}
            @isset($resumenHoy)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Citas de hoy</h3>
                        <div class="flex gap-2">
                            @permiso('CITA_REGISTRAR')
                                <a href="{{ route('citas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">Nueva cita</a>
                            @endpermiso
                            <x-boton-enlace :href="route('citas.index')">Ver citas</x-boton-enlace>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-3">
                        @foreach ($resumenHoy as $estado)
                            <a href="{{ route('citas.index', ['fecha' => today()->toDateString(), 'estado_cita_id' => $estado->id]) }}"
                               class="rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                                <div class="text-2xl font-semibold text-gray-900">{{ $estado->citas_count }}</div>
                                <x-estado-cita :estado="$estado->nombre" class="mt-1" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endisset
        </div>
    </div>
</x-app-layout>