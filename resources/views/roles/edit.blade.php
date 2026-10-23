<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar rol: {{ $rol->nombre }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('roles.update', $rol) }}" data-validar novalidate>
                    @csrf
                    @method('PUT')
                    @include('roles._form')

                    <div class="flex justify-end gap-3 mt-6">
                        <x-boton-enlace :href="route('roles.index')">Cancelar</x-boton-enlace>
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>