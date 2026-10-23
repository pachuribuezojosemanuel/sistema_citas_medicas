<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inicio</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <h3 class="text-lg font-semibold">Bienvenido(a), {{ Auth::user()->name }}</h3>
                <p class="mt-1 text-gray-600">Rol: {{ Auth::user()->rol->nombre }}</p>
                <p class="mt-4 text-gray-600">
                    Use el menú superior para acceder a las opciones disponibles para su rol.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>