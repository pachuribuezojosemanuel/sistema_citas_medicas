<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validación en el servidor para crear y editar usuarios.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El permiso USUARIO_GESTIONAR ya lo verifica el middleware de la ruta
        return true;
    }

    /**
     * Normaliza los datos antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'  => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');   // null al crear
        $esNuevo = $usuario === null;

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'name')->ignore($usuario?->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($usuario?->id),
            ],
            'rol_id' => [
                'required',
                'integer',
                Rule::exists('rols', 'id')->where('estado', true),
            ],
            // Al editar, la contraseña es opcional (vacía = no se cambia)
            'password' => [
                $esNuevo ? 'required' : 'nullable',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre de usuario solo puede tener letras, números, punto, guion y guion bajo (sin espacios).',
        ];
    }
}
