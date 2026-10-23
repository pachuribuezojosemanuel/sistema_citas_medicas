{{-- Mensajes de la sesión: ->with('exito', '...') o ->with('error', '...') --}}
@if (session('exito') || session('error'))
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-6">
        @if (session('exito'))
            <div class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('exito') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif
    </div>
@endif