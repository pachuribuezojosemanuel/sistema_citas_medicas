<?php

namespace App\Http\Requests;

use App\Models\Cita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Validación en el servidor del registro de citas (RF06, RN03, RN06).
 * La disponibilidad del horario la verifica CitaService al guardar.
 */
class CitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // CITA_REGISTRAR lo verifica el middleware
    }

    protected function prepareForValidation(): void
    {
        $motivo = trim((string) $this->input('motivo'));

        $this->merge([
            'motivo'      => $motivo !== '' ? $motivo : null,
            'hora_inicio' => substr((string) $this->input('hora_inicio'), 0, 5),
        ]);
    }

    public function rules(): array
    {
        return [
            'paciente_id'     => ['required', 'integer', Rule::exists('pacientes', 'id')->where('estado', true)],
            'especialidad_id' => ['required', 'integer', Rule::exists('especialidades', 'id')->where('estado', true)],
            'medico_id'       => [
                'required', 'integer',
                Rule::exists('medicos', 'id')->where('estado', true),
                // RN03: el médico debe ejercer la especialidad elegida
                function ($attribute, $value, $fail) {
                    $ejerce = DB::table('medico_especialidad')
                        ->where('medico_id', $value)
                        ->where('especialidad_id', $this->input('especialidad_id'))
                        ->exists();

                    if (! $ejerce) {
                        $fail('El médico seleccionado no atiende la especialidad elegida.');
                    }
                },
            ],
            'fecha'       => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'motivo'      => ['nullable', 'string', 'max:200'],
            'modalidad'   => ['required', Rule::in(Cita::MODALIDADES)],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required'     => 'Seleccione un paciente.',
            'paciente_id.exists'       => 'El paciente no existe o está inactivo.',
            'especialidad_id.required' => 'Seleccione una especialidad.',
            'especialidad_id.exists'   => 'La especialidad no existe o está inactiva.',
            'medico_id.required'       => 'Seleccione un médico.',
            'medico_id.exists'         => 'El médico no existe o está inactivo (no puede recibir nuevas citas).',
            'fecha.required'           => 'Seleccione la fecha de la cita.',
            'fecha.after_or_equal'     => 'No se pueden registrar citas en fechas pasadas.',
            'hora_inicio.required'     => 'Seleccione una hora disponible.',
            'hora_inicio.date_format'  => 'La hora seleccionada no es válida.',
            'modalidad.in'             => 'La modalidad debe ser Presencial o Virtual.',
        ];
    }
}