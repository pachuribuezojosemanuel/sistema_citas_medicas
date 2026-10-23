<x-guest-layout>
    <!-- Mensaje de estado de la sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-xl font-semibold text-gray-800 text-center mb-6">Iniciar sesión</h2>

    <form method="POST" action="{{ route('login') }}" id="form-login" novalidate>
        @csrf

        <!-- Usuario o correo -->
        <div>
            <x-input-label for="login" value="Usuario o correo electrónico" />
            <x-text-input id="login" class="block mt-1 w-full" type="text" name="login"
                          :value="old('login')" required autofocus autocomplete="username" maxlength="100" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
            <p class="text-sm text-red-600 mt-2 hidden" id="error-login"></p>
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="text-sm text-red-600 mt-2 hidden" id="error-password"></p>
        </div>

        <!-- Recordarme -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-600">Recordarme</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>Ingresar</x-primary-button>
        </div>
    </form>

    <!-- Validación en el cliente -->
    <script src="{{ asset('js/login.js') }}"></script>
</x-guest-layout>