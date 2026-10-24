<?php

namespace App\Http\Requests;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validación en el servidor de usuarios y datos del personal.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // USUARIO_GESTIONAR lo verifica el middleware
    }

    /**
     * ¿El usuario que se edita es la cuenta de un médico o paciente?
     */
    private function esVinculado(): bool
    {
        $usuario = $this->route('usuario');

        return $usuario !== null && ($usuario->medico()->exists() || $usuario->paciente()->exists());
    }

    protected function prepareForValidation(): void
    {
        $nombre = fn ($v) => mb_convert_case(preg_replace('/\s+/', ' ', trim((string) $v)), MB_CASE_TITLE, 'UTF-8');

        $this->merge([
            'name'      => trim((string) $this->input('name')),
            'email'     => mb_strtolower(trim((string) $this->input('email'))),
            'ci'        => mb_strtoupper(preg_replace('/\s+/', '', (string) $this->input('ci'))),
            'nombres'   => $nombre($this->input('nombres')),
            'apellidos' => $nombre($this->input('apellidos')),
            'cargo'     => preg_replace('/\s+/', ' ', trim((string) $this->input('cargo'))),
        ]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');   // null al crear
        $esNuevo = $usuario === null;
        $letras  = 'regex:/^[\pL\s]+$/u';

        // ---- Cuenta (siempre) ----
        $reglas = [
            'name' => [
                'required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'name')->ignore($usuario?->id),
            ],
            // Al editar, la contraseña es opcional (vacía = no se cambia)
            'password' => [
                $esNuevo ? 'required' : 'nullable',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ];

        // Cuenta de médico o paciente: solo usuario y contraseña
        if ($this->esVinculado()) {
            return $reglas;
        }

        // ---- Cuenta de personal ----
        $reglas['email'] = [
            'required', 'string', 'email', 'max:100',
            Rule::unique('users', 'email')->ignore($usuario?->id),
        ];
        $reglas['rol_id'] = [
            'required', 'integer',
            // Solo roles activos de personal (no Médico ni Paciente)
            Rule::exists('rols', 'id')->where('estado', true)->whereNotIn('nombre', Rol::ROLES_CON_MODULO),
        ];

        // ---- Datos del personal ----
        $reglas['ci'] = [
            'required', 'string', 'regex:/^[0-9A-Z-]{5,15}$/',
            Rule::unique('personals', 'ci')->ignore($usuario?->personal?->id),
        ];
        $reglas['nombres']       = ['required', 'string', 'min:2', 'max:60', $letras];
        $reglas['apellidos']     = ['required', 'string', 'min:2', 'max:60', $letras];
        $reglas['telefono']      = ['nullable', 'string', 'regex:/^[0-9+\s-]{7,20}$/'];
        $reglas['cargo']         = ['required', 'string', 'min:3', 'max:50'];
        $reglas['fecha_ingreso'] = ['nullable', 'date', 'before_or_equal:today'];

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'name.regex'      => 'El nombre de usuario solo puede tener letras, números, punto, guion y guion bajo (sin espacios).',
            'rol_id.exists'   => 'Seleccione un rol de personal activo. Médicos y pacientes se registran en sus propios módulos.',
            'ci.unique'       => 'Ya existe un miembro del personal con ese CI.',
            'ci.regex'        => 'El CI solo puede contener números, letras y guiones (5 a 15 caracteres).',
            'nombres.regex'   => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'telefono.regex'  => 'Ingrese un teléfono válido (7 a 20 dígitos).',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fecha_ingreso' => 'fecha de ingreso',
        ];
    }
}