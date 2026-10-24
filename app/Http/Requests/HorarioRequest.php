<?php

namespace App\Http\Requests;

use App\Models\HorarioAtencion;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validación en el servidor de horarios de atención (RF05).
 */
class HorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // HORARIO_GESTIONAR lo verifica el middleware
    }

    /**
     * Las horas llegan como "08:00" (algunos navegadores envían "08:00:00").
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'hora_inicio' => substr((string) $this->input('hora_inicio'), 0, 5),
            'hora_fin'    => substr((string) $this->input('hora_fin'), 0, 5),
        ]);
    }

    public function rules(): array
    {
        return [
            'medico_id'      => ['required', 'integer', Rule::exists('medicos', 'id')->where('estado', true)],
            'dia_semana_id'  => ['required', 'integer', 'exists:dia_semanas,id'],
            'consultorio_id' => ['required', 'integer', Rule::exists('consultorios', 'id')->where('estado', true)],
            'hora_inicio'    => ['required', 'date_format:H:i', 'regex:/^\d{2}:(00|15|30|45)$/'],
            'hora_fin'       => ['required', 'date_format:H:i', 'after:hora_inicio', 'regex:/^\d{2}:(00|15|30|45)$/'],
            'duracion_cita'  => ['required', 'integer', Rule::in([15, 20, 30, 45, 60])],
        ];
    }

    /**
     * Reglas que dependen de varios campos o de la base de datos.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $inicio   = Carbon::createFromFormat('H:i', $this->hora_inicio);
                $fin      = Carbon::createFromFormat('H:i', $this->hora_fin);
                $minutos  = (int) $inicio->diffInMinutes($fin);
                $duracion = (int) $this->duracion_cita;

                // Las citas deben respetar los intervalos configurados
                if ($minutos % $duracion !== 0) {
                    $validator->errors()->add(
                        'hora_fin',
                        "El rango ({$minutos} min) debe ser múltiplo de la duración de la cita ({$duracion} min)."
                    );
                    return;
                }

                $ignorar = $this->route('horario')?->id;

                // El mismo médico no puede tener dos horarios superpuestos el mismo día
                $choque = HorarioAtencion::buscarChoque(
                    'medico_id',
                    (int) $this->medico_id,
                    (int) $this->dia_semana_id,
                    $this->hora_inicio,
                    $this->hora_fin,
                    $ignorar
                );
                if ($choque) {
                    $validator->errors()->add(
                        'hora_inicio',
                        "El médico ya tiene un horario ese día de {$choque->rango} que se superpone."
                    );
                }

                // El mismo consultorio no puede estar ocupado por dos médicos a la vez
                $choque = HorarioAtencion::buscarChoque(
                    'consultorio_id',
                    (int) $this->consultorio_id,
                    (int) $this->dia_semana_id,
                    $this->hora_inicio,
                    $this->hora_fin,
                    $ignorar
                );
                if ($choque) {
                    $validator->errors()->add(
                        'consultorio_id',
                        "El consultorio {$choque->consultorio->numero} está ocupado ese día de {$choque->rango} por {$choque->medico->nombre_completo}."
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'medico_id.exists'      => 'Seleccione un médico activo.',
            'consultorio_id.exists' => 'Seleccione un consultorio activo.',
            'hora_inicio.date_format' => 'Ingrese una hora válida (HH:MM).',
            'hora_fin.date_format'  => 'Ingrese una hora válida (HH:MM).',
            'hora_fin.after'        => 'La hora final debe ser posterior a la hora inicial.',
            'duracion_cita.in'      => 'Seleccione una duración válida.',
            'hora_inicio.regex' => 'Use horas en intervalos de 15 minutos (ej.: 08:00, 08:15, 08:30).',
            'hora_fin.regex'    => 'Use horas en intervalos de 15 minutos (ej.: 12:00, 12:15, 12:30).',
        ];
    }

    public function attributes(): array
    {
        return [
            'medico_id'      => 'médico',
            'dia_semana_id'  => 'día',
            'consultorio_id' => 'consultorio',
            'duracion_cita'  => 'duración de la cita',
        ];
    }
}
