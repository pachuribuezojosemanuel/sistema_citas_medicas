<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validación en el servidor de médicos (RF03).
 */
class MedicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // MEDICO_GESTIONAR lo verifica el middleware
    }

    protected function prepareForValidation(): void
    {
        $limpiar = fn($v) => mb_convert_case(preg_replace('/\s+/', ' ', trim((string) $v)), MB_CASE_TITLE, 'UTF-8');

        $this->merge([
            'nombres'              => $limpiar($this->input('nombres')),
            'apellidos'            => $limpiar($this->input('apellidos')),
            'registro_profesional' => mb_strtoupper(preg_replace('/\s+/', '', (string) $this->input('registro_profesional'))),
            'telefono'             => trim((string) $this->input('telefono')),
            'correo'               => mb_strtolower(trim((string) $this->input('correo'))),
            'name'                 => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        $medico  = $this->route('medico');   // null al crear
        $esNuevo = $medico === null;

        $reglas = [
            'nombres'   => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL\s]+$/u'],
            'apellidos' => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL\s]+$/u'],
            'registro_profesional' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9\/-]+$/',
                Rule::unique('medicos', 'registro_profesional')->ignore($medico?->id),
            ],
            'telefono' => ['required', 'string', 'regex:/^[0-9+\s-]{7,20}$/'],
            'correo'   => [
                'required',
                'email',
                'max:100',
                Rule::unique('medicos', 'correo')->ignore($medico?->id),
                // El correo también es el de su cuenta de usuario
                Rule::unique('users', 'email')->ignore($medico?->user_id),
            ],
            'especialidades'   => ['required', 'array', 'min:1'],
            'especialidades.*' => ['integer', 'exists:especialidades,id'],
        ];

        // Cuenta de acceso: solo al registrar
        if ($esNuevo) {
            $reglas['name']     = ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,name'];
            $reglas['password'] = ['required', 'confirmed', Password::min(8)->letters()->numbers()];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'nombres.regex'              => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos.regex'            => 'Los apellidos solo pueden contener letras y espacios.',
            'registro_profesional.regex' => 'El registro profesional solo puede contener letras, números, guiones y "/".',
            'registro_profesional.unique' => 'Ya existe un médico con ese registro profesional.',
            'telefono.regex'             => 'Ingrese un teléfono válido (7 a 20 dígitos).',
            'correo.unique'              => 'Ese correo ya está registrado.',
            'especialidades.required'    => 'Seleccione al menos una especialidad.',
            'especialidades.min'         => 'Seleccione al menos una especialidad.',
            'name.regex'                 => 'El nombre de usuario solo puede tener letras, números, punto, guion y guion bajo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'registro_profesional' => 'registro profesional',
            'correo'               => 'correo',
            'especialidades'       => 'especialidades',
        ];
    }
}
