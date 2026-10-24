<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar horario de atención</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="mb-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-3">
                    Las citas ya registradas no se modifican al cambiar el horario.
                </p>

                <form method="POST" action="{{ route('horarios.update', $horario) }}" data-validar novalidate>
                    @csrf
                    @method('PUT')
                    @include('horarios._form')

                    <div class="flex justify-end gap-3 mt-6">
                        <x-boton-enlace :href="route('horarios.index')">Cancelar</x-boton-enlace>
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>