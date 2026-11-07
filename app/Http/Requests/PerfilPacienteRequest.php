<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Datos que el propio paciente puede actualizar desde el portal.
 */
class PerfilPacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // MIS_CITAS lo verifica el middleware
    }

    protected function prepareForValidation(): void
    {
        $nombre = fn ($v) => mb_convert_case(preg_replace('/\s+/', ' ', trim((string) $v)), MB_CASE_TITLE, 'UTF-8');

        $contactos = collect($this->input('contactos', []))
            ->map(fn ($c) => [
                'nombre_completo' => $nombre($c['nombre_completo'] ?? ''),
                'parentesco'      => trim((string) ($c['parentesco'] ?? '')),
                'telefono'        => trim((string) ($c['telefono'] ?? '')),
            ])
            ->filter(fn ($c) => $c['nombre_completo'] !== '' || $c['parentesco'] !== '' || $c['telefono'] !== '')
            ->values()
            ->all();

        $direccion = trim((string) $this->input('direccion'));

        $this->merge([
            'telefono'  => trim((string) $this->input('telefono')),
            'direccion' => $direccion !== '' ? $direccion : null,
            'contactos' => $contactos,
        ]);
    }

    public function rules(): array
    {
        return [
            'telefono'                    => ['required', 'string', 'regex:/^[0-9+\s-]{7,20}$/'],
            'direccion'                   => ['nullable', 'string', 'max:200'],
            'contactos'                   => ['nullable', 'array', 'max:3'],
            'contactos.*.nombre_completo' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'contactos.*.parentesco'      => ['required', 'string', 'max:30'],
            'contactos.*.telefono'        => ['required', 'string', 'regex:/^[0-9+\s-]{7,20}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex'                       => 'Ingrese un teléfono válido (7 a 20 dígitos).',
            'contactos.max'                        => 'Puede registrar hasta 3 contactos de emergencia.',
            'contactos.*.nombre_completo.required' => 'Ingrese el nombre del contacto.',
            'contactos.*.nombre_completo.regex'    => 'El nombre del contacto solo puede contener letras.',
            'contactos.*.parentesco.required'      => 'Ingrese el parentesco.',
            'contactos.*.telefono.required'        => 'Ingrese el teléfono del contacto.',
            'contactos.*.telefono.regex'           => 'Teléfono del contacto no válido.',
        ];
    }
}