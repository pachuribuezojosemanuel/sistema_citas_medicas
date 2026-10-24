<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Cálculo de horarios disponibles de un médico (RF05).
 */
class DisponibilidadService
{
    /**
     * Devuelve todos los bloques de un médico para una fecha.
     * Cada bloque: ['inicio' => '08:00', 'fin' => '08:30', 'consultorio' => 'C-101', 'libre' => true]
     *
     * @param int|null $ignorarCitaId  Cita a ignorar (al reprogramar, su propio horario cuenta como libre)
     */
    public function bloquesDelDia(Medico $medico, Carbon $fecha, ?int $ignorarCitaId = null): array
    {
        // RN04: un médico inactivo no recibe nuevas citas
        if (! $medico->estado) {
            return [];
        }

        // RN06: no se ofrecen fechas pasadas
        if ($fecha->copy()->startOfDay()->lt(today())) {
            return [];
        }

        // RN05: solo los horarios activos de ese día de la semana (1 = Lunes ... 7 = Domingo)
        $horarios = $medico->horarios()
            ->with('consultorio')
            ->where('estado', true)
            ->where('dia_semana_id', $fecha->dayOfWeekIso)
            ->orderBy('hora_inicio')
            ->get();

        $ocupadas = $this->citasOcupadas($medico->id, $fecha, $ignorarCitaId);
        $ahora    = now();
        $bloques  = [];

        foreach ($horarios as $horario) {
            $inicio     = $fecha->copy()->setTimeFromTimeString($horario->hora_inicio);
            $finHorario = $fecha->copy()->setTimeFromTimeString($horario->hora_fin);

            // RN08: bloques del tamaño configurado
            while ($inicio->copy()->addMinutes($horario->duracion_cita)->lte($finHorario)) {
                $fin = $inicio->copy()->addMinutes($horario->duracion_cita);

                $libre = $inicio->gt($ahora)   // hoy: no se ofrecen horas que ya pasaron
                    && ! $this->seSuperpone($inicio->format('H:i:s'), $fin->format('H:i:s'), $ocupadas);

                $bloques[] = [
                    'inicio'      => $inicio->format('H:i'),
                    'fin'         => $fin->format('H:i'),
                    'consultorio' => $horario->consultorio->numero,
                    'libre'       => $libre,
                ];

                $inicio = $fin;
            }
        }

        return $bloques;
    }

    /**
     * Solo los bloques libres.
     */
    public function bloquesLibres(Medico $medico, Carbon $fecha, ?int $ignorarCitaId = null): array
    {
        return array_values(array_filter(
            $this->bloquesDelDia($medico, $fecha, $ignorarCitaId),
            fn ($b) => $b['libre']
        ));
    }

    /**
     * Verifica en el servidor que un horario concreto esté disponible (RN13).
     * Se usará al guardar y reprogramar citas.
     */
    public function estaDisponible(Medico $medico, Carbon $fecha, string $horaInicio, ?int $ignorarCitaId = null): bool
    {
        $horaInicio = substr($horaInicio, 0, 5);

        foreach ($this->bloquesLibres($medico, $fecha, $ignorarCitaId) as $bloque) {
            if ($bloque['inicio'] === $horaInicio) {
                return true;
            }
        }

        return false;
    }

    /**
     * Citas que ocupan horario ese día. RN09: las canceladas no ocupan.
     */
    private function citasOcupadas(int $medicoId, Carbon $fecha, ?int $ignorarCitaId): Collection
    {
        return Cita::where('medico_id', $medicoId)
            ->whereDate('fecha', $fecha->toDateString())
            ->where('estado_cita_id', '!=', EstadoCita::idDe(EstadoCita::CANCELADA))
            ->when($ignorarCitaId, fn ($q) => $q->where('id', '!=', $ignorarCitaId))
            ->get(['hora_inicio', 'hora_fin']);
    }

    /**
     * RN07: dos intervalos se superponen si uno empieza antes de que el otro termine.
     */
    private function seSuperpone(string $inicio, string $fin, Collection $ocupadas): bool
    {
        foreach ($ocupadas as $cita) {
            if ($inicio < $cita->hora_fin && $fin > $cita->hora_inicio) {
                return true;
            }
        }

        return false;
    }
}