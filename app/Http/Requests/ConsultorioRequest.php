<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación en el servidor de consultorios.
 */
class ConsultorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // CONSULTORIO_GESTIONAR lo verifica el middleware
    }

    /**
     * Número en mayúsculas y sin espacios: " c-101 " → "C-101"
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'numero' => mb_strtoupper(preg_replace('/\s+/', '', (string) $this->input('numero'))),
            'nombre' => preg_replace('/\s+/', ' ', trim((string) $this->input('nombre'))),
            'piso'   => trim((string) $this->input('piso')) ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'numero' => [
                'required', 'string', 'max:10',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('consultorios', 'numero')->ignore($this->route('consultorio')?->id),
            ],
            'nombre' => ['required', 'string', 'min:3', 'max:50'],
            'piso'   => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.regex'  => 'El número solo puede contener letras, números y guiones. Ej.: C-101',
            'numero.unique' => 'Ya existe un consultorio con ese número.',
        ];
    }

    public function attributes(): array
    {
        return ['numero' => 'número'];
    }
}