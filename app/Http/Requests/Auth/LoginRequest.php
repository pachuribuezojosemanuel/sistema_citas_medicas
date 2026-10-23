<?php

namespace App\Http\Requests\Auth;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validación en el servidor.
     */
    public function rules(): array
    {
        return [
            'login'    => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required'    => 'Ingrese su nombre de usuario o correo.',
            'password.required' => 'Ingrese su contraseña.',
        ];
    }

    /**
     * Autentica al usuario con nombre de usuario o correo (RF01).
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim($this->input('login'));

        // Buscar por correo o por nombre de usuario
        $campo = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $user  = User::with('rol')->where($campo, $login)->first();

        // Verificar la contraseña con password_verify() (RF01)
        if (! $user || ! password_verify($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            Bitacora::registrar('LOGIN_FALLIDO', 'Autenticación', "Intento fallido con: {$login}", $user?->id);

            // Mensaje genérico: no revela si falló el usuario o la contraseña
            throw ValidationException::withMessages([
                'login' => 'Credenciales incorrectas.',
            ]);
        }

        // Usuario o rol desactivado
        if (! $user->estado || ! $user->rol?->estado) {
            Bitacora::registrar('LOGIN_FALLIDO', 'Autenticación', 'Cuenta o rol inactivo', $user->id);

            throw ValidationException::withMessages([
                'login' => 'Su cuenta está desactivada. Comuníquese con el administrador.',
            ]);
        }

        // Iniciar sesión
        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());

        $user->update(['ultimo_acceso' => now()]);
        Bitacora::registrar('LOGIN', 'Autenticación', 'Inicio de sesión exitoso', $user->id);
    }

    /**
     * Bloquea temporalmente tras 5 intentos fallidos.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $segundos = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => "Demasiados intentos. Intente de nuevo en {$segundos} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')) . '|' . $this->ip());
    }
}