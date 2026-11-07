<?php

namespace App\Services;

use App\Exceptions\CitaException;
use App\Models\Cancelacion;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\HistorialEstadoCita;
use App\Models\Medico;
use App\Models\Reprogramacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Lógica de negocio de las citas (RF06 a RF10).
 */
class CitaService
{
    /**
     * Llave del bloqueo de PostgreSQL para las operaciones de agenda.
     * Mientras una transacción de citas está en curso, otra espera su turno;
     * así dos recepcionistas no pueden tomar el mismo horario al mismo tiempo (RF07).
     */
    private const LLAVE_BLOQUEO = 20261;

    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    // ==================================================================
    // RF06 + RF07 — Registrar
    // ==================================================================

    public function registrar(array $datos, User $usuario): Cita
    {
        return DB::transaction(function () use ($datos, $usuario) {
            $this->bloquearAgenda();

            $medico = Medico::findOrFail($datos['medico_id']);
            $fecha  = Carbon::parse($datos['fecha'])->startOfDay();

            // Se vuelve a comprobar la disponibilidad al guardar (RF06, RN13)
            $bloque = $this->bloqueLibre($medico, $fecha, $datos['hora_inicio']);
            $this->verificarPaciente((int) $datos['paciente_id'], $fecha, $bloque);

            $cita = Cita::create([
                'codigo'          => Cita::siguienteCodigo(),
                'paciente_id'     => $datos['paciente_id'],
                'medico_id'       => $medico->id,
                'especialidad_id' => $datos['especialidad_id'],
                'estado_cita_id'  => EstadoCita::idDe(EstadoCita::PROGRAMADA),
                'user_id'         => $usuario->id,
                'fecha'           => $fecha->toDateString(),
                'hora_inicio'     => $bloque['inicio'],
                'hora_fin'        => $bloque['fin'],       // RN08: la duración la define el horario
                'motivo'          => $datos['motivo'] ?? null,
                'modalidad'       => $datos['modalidad'],
            ]);

            $this->registrarHistorial($cita, null, EstadoCita::PROGRAMADA, $usuario, 'Registro de la cita');

            return $cita;
        });
    }

    // ==================================================================
    // RF10 — Cambiar estado (Confirmada, Atendida, Inasistencia)
    // ==================================================================

    /**
     * @return array{0: string, 1: string} [estado anterior, estado nuevo]
     */
    public function cambiarEstado(Cita $cita, string $nuevo, ?string $observacion, User $usuario): array
    {
        if ($nuevo === EstadoCita::CANCELADA) {
            throw new CitaException('Para cancelar una cita use la opción "Cancelar cita" e indique el motivo.');
        }

        return DB::transaction(function () use ($cita, $nuevo, $observacion, $usuario) {
            // Bloquea la fila de la cita mientras se cambia su estado
            $cita = Cita::with('estado')->lockForUpdate()->findOrFail($cita->id);
            $actual = $cita->estado->nombre;

            if (! $cita->puedeCambiarA($nuevo)) {
                throw new CitaException("Una cita en estado {$actual} no puede pasar a {$nuevo}.");
            }

            // Solo se registra la atención o la inasistencia cuando la cita ya empezó
            if (in_array($nuevo, [EstadoCita::ATENDIDA, EstadoCita::INASISTENCIA], true) && $cita->inicio()->isFuture()) {
                throw new CitaException(
                    "La cita es el {$cita->fecha->format('d/m/Y')} a las " . substr($cita->hora_inicio, 0, 5) .
                    ". Solo puede marcarse como {$nuevo} a partir de esa hora."
                );
            }

            $cita->update(['estado_cita_id' => EstadoCita::idDe($nuevo)]);
            $this->registrarHistorial($cita, $actual, $nuevo, $usuario, $observacion);

            return [$actual, $nuevo];
        });
    }

    // ==================================================================
    // RF08 — Reprogramar
    // ==================================================================

    public function reprogramar(Cita $cita, array $datos, User $usuario): Cita
    {
        return DB::transaction(function () use ($cita, $datos, $usuario) {
            $this->bloquearAgenda();

            $cita = Cita::with(['estado', 'medico'])->lockForUpdate()->findOrFail($cita->id);

            // Política: solo citas Programadas o Confirmadas
            if (! $cita->esModificable()) {
                throw new CitaException(
                    "Una cita {$cita->estado->nombre} no puede reprogramarse. Registre una nueva cita; la anterior queda en el historial."
                );
            }

            $fecha = Carbon::parse($datos['fecha'])->startOfDay();
            $hora  = substr($datos['hora_inicio'], 0, 5);

            if ($fecha->isSameDay($cita->fecha) && $hora === substr($cita->hora_inicio, 0, 5)) {
                throw new CitaException('Elija una fecha u hora diferente a la actual.');
            }

            // Verifica el nuevo horario ignorando la propia cita (RN13)
            $bloque = $this->bloqueLibre($cita->medico, $fecha, $hora, $cita->id);
            $this->verificarPaciente($cita->paciente_id, $fecha, $bloque, $cita->id);

            Reprogramacion::create([
                'cita_id'        => $cita->id,
                'user_id'        => $usuario->id,
                'fecha_anterior' => $cita->fecha->toDateString(),
                'hora_anterior'  => $cita->hora_inicio,
                'fecha_nueva'    => $fecha->toDateString(),
                'hora_nueva'     => $bloque['inicio'],
                'motivo'         => $datos['motivo'],
                'fecha_registro' => now(),
            ]);

            $cita->update([
                'fecha'       => $fecha->toDateString(),
                'hora_inicio' => $bloque['inicio'],
                'hora_fin'    => $bloque['fin'],
            ]);

            return $cita;
        });
    }

    // ==================================================================
    // RF09 — Cancelar
    // ==================================================================

    public function cancelar(Cita $cita, string $motivo, User $usuario): Cita
    {
        return DB::transaction(function () use ($cita, $motivo, $usuario) {
            $cita = Cita::with('estado')->lockForUpdate()->findOrFail($cita->id);
            $actual = $cita->estado->nombre;

            // Una cita atendida no puede cancelarse "como si nunca hubiera ocurrido"
            if (! $cita->puedeCambiarA(EstadoCita::CANCELADA)) {
                throw new CitaException("Una cita en estado {$actual} no puede cancelarse.");
            }

            Cancelacion::create([
                'cita_id'           => $cita->id,
                'user_id'           => $usuario->id,
                'motivo'            => $motivo,
                'fecha_cancelacion' => now(),
            ]);

            // No se elimina: cambia de estado y deja de ocupar el horario (RN09)
            $cita->update(['estado_cita_id' => EstadoCita::idDe(EstadoCita::CANCELADA)]);
            $this->registrarHistorial($cita, $actual, EstadoCita::CANCELADA, $usuario, "Cancelada: {$motivo}");

            return $cita;
        });
    }

    // ==================================================================
    // Apoyo
    // ==================================================================

    /**
     * Bloqueo transaccional de PostgreSQL (se libera solo al terminar la transacción).
     */
    private function bloquearAgenda(): void
    {
        DB::select('SELECT pg_advisory_xact_lock(?)', [self::LLAVE_BLOQUEO]);
    }

    /**
     * Devuelve el bloque del horario que empieza a la hora indicada, si está libre.
     * Así se garantiza RN05 (dentro del horario) y RN08 (intervalos configurados).
     */
    private function bloqueLibre(Medico $medico, Carbon $fecha, string $hora, ?int $ignorarCitaId = null): array
    {
        if (! $medico->estado) {
            throw new CitaException('El médico está inactivo y no puede recibir nuevas citas.');
        }

        if ($fecha->lt(today())) {
            throw new CitaException('No se pueden registrar citas en fechas pasadas.');
        }

        $hora = substr($hora, 0, 5);

        foreach ($this->disponibilidad->bloquesDelDia($medico, $fecha, $ignorarCitaId) as $bloque) {
            if ($bloque['inicio'] === $hora) {
                if (! $bloque['libre']) {
                    throw new CitaException(
                        "El horario {$bloque['inicio']}–{$bloque['fin']} ya no está disponible para el médico. Elija otro horario."
                    );
                }

                return $bloque;
            }
        }

        throw new CitaException(
            "La hora {$hora} del {$fecha->format('d/m/Y')} está fuera del horario de atención del médico."
        );
    }

    /**
     * El paciente no puede tener otra cita (no cancelada) que se superponga.
     */
    private function verificarPaciente(int $pacienteId, Carbon $fecha, array $bloque, ?int $ignorarCitaId = null): void
    {
        $choque = Cita::where('paciente_id', $pacienteId)
            ->whereDate('fecha', $fecha->toDateString())
            ->where('estado_cita_id', '!=', EstadoCita::idDe(EstadoCita::CANCELADA))
            ->when($ignorarCitaId, fn ($q) => $q->where('id', '!=', $ignorarCitaId))
            ->where('hora_inicio', '<', $bloque['fin'])
            ->where('hora_fin', '>', $bloque['inicio'])
            ->first();

        if ($choque) {
            throw new CitaException("El paciente ya tiene la cita {$choque->codigo} de {$choque->horario} ese día.");
        }
    }

    /**
     * Guarda un registro en el historial de estados (RF10).
     */
    private function registrarHistorial(Cita $cita, ?string $anterior, string $nuevo, User $usuario, ?string $observacion): void
    {
        HistorialEstadoCita::create([
            'cita_id'            => $cita->id,
            'estado_anterior_id' => $anterior ? EstadoCita::idDe($anterior) : null,
            'estado_nuevo_id'    => EstadoCita::idDe($nuevo),
            'user_id'            => $usuario->id,
            'fecha_cambio'       => now(),
            'observacion'        => $observacion,
        ]);
    }
}