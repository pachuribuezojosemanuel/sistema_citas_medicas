<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación en el servidor de la reprogramación (RF08).
 */
class ReprogramarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // CITA_REPROGRAMAR lo verifica el middleware
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'motivo'      => trim((string) $this->input('motivo')),
            'hora_inicio' => substr((string) $this->input('hora_inicio'), 0, 5),
        ]);
    }

    public function rules(): array
    {
        return [
            'fecha'       => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'motivo'      => ['required', 'string', 'min:5', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required'          => 'Seleccione la nueva fecha.',
            'fecha.after_or_equal'    => 'No se puede reprogramar a una fecha pasada.',
            'hora_inicio.required'    => 'Seleccione una hora disponible.',
            'motivo.required'         => 'Indique el motivo de la reprogramación.',
            'motivo.min'              => 'Describa el motivo con al menos 5 caracteres.',
        ];
    }
}