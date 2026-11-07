<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\EstadoCita;
use App\Models\HistorialEstadoCita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CitaSeeder extends Seeder
{
    public function run(): void
    {
        if (Cita::exists()) {
            return;   // ya hay citas: no se duplican
        }

        $recepcion = User::where('name', 'recepcion')->firstOrFail();
        $perez     = Medico::where('registro_profesional', 'MP-10001')->firstOrFail();   // lunes 08–12, miércoles 14–18
        $rojas     = Medico::where('registro_profesional', 'MP-10002')->firstOrFail();   // martes y jueves 09–13
        $general   = Especialidad::where('nombre', 'Medicina general')->firstOrFail();
        $pediatria = Especialidad::where('nombre', 'Pediatría')->firstOrFail();
        $maria     = Paciente::where('ci', '7845123')->firstOrFail();
        $pedro     = Paciente::where('ci', '5632147')->firstOrFail();
        $lucia     = Paciente::where('ci', '14523698')->firstOrFail();

        $lunesPasado  = today()->previous(Carbon::MONDAY);
        $martesPasado = today()->previous(Carbon::TUESDAY);
        $lunesProximo = today()->next(Carbon::MONDAY);
        $martesProx   = today()->next(Carbon::TUESDAY);

        // [paciente, médico, especialidad, fecha, inicio, fin, motivo, estados por los que pasó]
        $citas = [
            [$maria, $perez, $general,   $lunesPasado,  '08:00', '08:30', 'Dolor de cabeza frecuente', [EstadoCita::CONFIRMADA, EstadoCita::ATENDIDA]],
            [$pedro, $perez, $general,   $lunesPasado,  '08:30', '09:00', 'Control de glucosa',        [EstadoCita::INASISTENCIA]],
            [$lucia, $rojas, $pediatria, $martesPasado, '09:00', '09:30', 'Control de asma',           [EstadoCita::ATENDIDA]],
            [$maria, $perez, $general,   $lunesProximo, '08:00', '08:30', 'Control de resultados',     []],
            [$pedro, $perez, $general,   $lunesProximo, '09:00', '09:30', 'Control de glucosa',        [EstadoCita::CONFIRMADA]],
            [$lucia, $rojas, $pediatria, $martesProx,   '10:00', '10:30', 'Control de crecimiento',    []],
        ];

        foreach ($citas as [$paciente, $medico, $especialidad, $fecha, $inicio, $fin, $motivo, $estados]) {
            $final = end($estados) ?: EstadoCita::PROGRAMADA;

            $cita = Cita::create([
                'codigo'          => Cita::siguienteCodigo(),
                'paciente_id'     => $paciente->id,
                'medico_id'       => $medico->id,
                'especialidad_id' => $especialidad->id,
                'estado_cita_id'  => EstadoCita::idDe($final),
                'user_id'         => $recepcion->id,
                'fecha'           => $fecha->toDateString(),
                'hora_inicio'     => $inicio,
                'hora_fin'        => $fin,
                'motivo'          => $motivo,
                'modalidad'       => 'Presencial',
            ]);

            // Historial: registro + cada cambio de estado
            $anterior = null;
            foreach (array_merge([EstadoCita::PROGRAMADA], $estados) as $estado) {
                HistorialEstadoCita::create([
                    'cita_id'            => $cita->id,
                    'estado_anterior_id' => $anterior ? EstadoCita::idDe($anterior) : null,
                    'estado_nuevo_id'    => EstadoCita::idDe($estado),
                    'user_id'            => $recepcion->id,
                    'fecha_cambio'       => now(),
                    'observacion'        => $anterior ? 'Datos de prueba' : 'Registro de la cita',
                ]);
                $anterior = $estado;
            }
        }
    }
}