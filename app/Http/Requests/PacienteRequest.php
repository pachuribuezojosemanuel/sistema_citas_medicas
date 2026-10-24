<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validación en el servidor de pacientes (RF02).
 */
class PacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // PACIENTE_GESTIONAR lo verifica el middleware
    }

    /**
     * Normaliza los datos antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $nombre  = fn ($v) => mb_convert_case(preg_replace('/\s+/', ' ', trim((string) $v)), MB_CASE_TITLE, 'UTF-8');
        $paciente = $this->route('paciente');

        // Contactos: se descartan las filas completamente vacías
        $contactos = collect($this->input('contactos', []))
            ->map(fn ($c) => [
                'nombre_completo' => $nombre($c['nombre_completo'] ?? ''),
                'parentesco'      => trim((string) ($c['parentesco'] ?? '')),
                'telefono'        => trim((string) ($c['telefono'] ?? '')),
            ])
            ->filter(fn ($c) => $c['nombre_completo'] !== '' || $c['parentesco'] !== '' || $c['telefono'] !== '')
            ->values()
            ->all();

        $correo = mb_strtolower(trim((string) $this->input('correo')));

        $this->merge([
            'ci'        => mb_strtoupper(preg_replace('/\s+/', '', (string) $this->input('ci'))),
            'nombres'   => $nombre($this->input('nombres')),
            'apellidos' => $nombre($this->input('apellidos')),
            'telefono'  => trim((string) $this->input('telefono')),
            'correo'    => $correo !== '' ? $correo : null,
            'contactos' => $contactos,
            // Si ya tiene cuenta, no se crea otra
            'crear_cuenta' => $paciente?->user_id ? false : $this->boolean('crear_cuenta'),
            'name'      => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        $paciente     = $this->route('paciente');   // null al crear
        $tieneCuenta  = (bool) $paciente?->user_id;
        $creaCuenta   = $this->boolean('crear_cuenta');
        $letras       = 'regex:/^[\pL\s]+$/u';

        // El correo es obligatorio y único en users solo si el paciente tiene (o tendrá) cuenta
        $reglasCorreo = ['nullable', 'email', 'max:100'];
        if ($tieneCuenta || $creaCuenta) {
            $reglasCorreo[0] = 'required';
            $reglasCorreo[]  = Rule::unique('users', 'email')->ignore($paciente?->user_id);
        }

        return [
            // ---- Datos personales ----
            'ci' => [
                'required', 'string', 'regex:/^[0-9A-Z-]{5,15}$/',
                Rule::unique('pacientes', 'ci')->ignore($paciente?->id),
            ],
            'nombres'          => ['required', 'string', 'min:2', 'max:60', $letras],
            'apellidos'        => ['required', 'string', 'min:2', 'max:60', $letras],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'sexo_id'          => ['required', 'integer', 'exists:sexos,id'],

            // ---- Contacto ----
            'telefono'  => ['required', 'string', 'regex:/^[0-9+\s-]{7,20}$/'],
            'correo'    => $reglasCorreo,
            'direccion' => ['nullable', 'string', 'max:200'],

            // ---- Información médica ----
            'tipo_sangre_id'    => ['nullable', 'integer', 'exists:tipo_sangres,id'],
            'medicamentos'      => ['nullable', 'string', 'max:2000'],
            'antecedentes'      => ['nullable', 'string', 'max:2000'],

            'alergias'          => ['nullable', 'array'],
            'alergias.*'        => ['integer', 'exists:alergias,id'],
            'obs_alergia'       => ['nullable', 'array'],
            'obs_alergia.*'     => ['nullable', 'string', 'max:200'],

            'enfermedades'      => ['nullable', 'array'],
            'enfermedades.*'    => ['integer', 'exists:enfermedades,id'],
            'diag_enfermedad'   => ['nullable', 'array'],
            'diag_enfermedad.*' => ['nullable', 'date', 'before_or_equal:today'],
            'obs_enfermedad'    => ['nullable', 'array'],
            'obs_enfermedad.*'  => ['nullable', 'string', 'max:200'],

            // ---- Contactos de emergencia (hasta 3) ----
            'contactos'                   => ['nullable', 'array', 'max:3'],
            'contactos.*.nombre_completo' => ['required', 'string', 'max:100', $letras],
            'contactos.*.parentesco'      => ['required', 'string', 'max:30'],
            'contactos.*.telefono'        => ['required', 'string', 'regex:/^[0-9+\s-]{7,20}$/'],

            // ---- Cuenta de acceso (opcional) ----
            'crear_cuenta' => ['boolean'],
            'name'     => ['exclude_unless:crear_cuenta,true', 'required', 'string', 'min:3', 'max:50',
                           'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,name'],
            'password' => ['exclude_unless:crear_cuenta,true', 'required', 'confirmed',
                           Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'ci.unique'                => 'Ya existe un paciente registrado con ese CI.',
            'ci.regex'                 => 'El CI solo puede contener números, letras y guiones (5 a 15 caracteres).',
            'nombres.regex'            => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos.regex'          => 'Los apellidos solo pueden contener letras y espacios.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'fecha_nacimiento.after'   => 'Ingrese una fecha de nacimiento válida.',
            'telefono.regex'           => 'Ingrese un teléfono válido (7 a 20 dígitos).',
            'correo.required'          => 'El correo es obligatorio para la cuenta de acceso.',
            'correo.unique'            => 'Ese correo ya está registrado en otra cuenta.',
            'contactos.max'            => 'Puede registrar hasta 3 contactos de emergencia.',
            'contactos.*.nombre_completo.required' => 'Ingrese el nombre del contacto.',
            'contactos.*.nombre_completo.regex'    => 'El nombre del contacto solo puede contener letras.',
            'contactos.*.parentesco.required'      => 'Ingrese el parentesco.',
            'contactos.*.telefono.required'        => 'Ingrese el teléfono del contacto.',
            'contactos.*.telefono.regex'           => 'Teléfono del contacto no válido.',
            'diag_enfermedad.*.before_or_equal'    => 'La fecha de diagnóstico no puede ser futura.',
            'name.regex'               => 'El nombre de usuario solo puede tener letras, números, punto, guion y guion bajo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sexo_id'        => 'sexo',
            'tipo_sangre_id' => 'tipo de sangre',
            'correo'         => 'correo',
            'direccion'      => 'dirección',
        ];
    }
}