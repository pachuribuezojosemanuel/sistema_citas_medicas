<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'codigo', 'paciente_id', 'medico_id', 'especialidad_id', 'estado_cita_id', 'user_id',
        'fecha', 'hora_inicio', 'hora_fin', 'motivo', 'modalidad',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public const MODALIDADES = ['Presencial', 'Virtual'];

    /**
     * Transiciones de estado permitidas (RF10).
     * Atendida, Cancelada e Inasistencia son estados finales.
     */
    public const TRANSICIONES = [
        EstadoCita::PROGRAMADA   => [EstadoCita::CONFIRMADA, EstadoCita::ATENDIDA, EstadoCita::INASISTENCIA, EstadoCita::CANCELADA],
        EstadoCita::CONFIRMADA   => [EstadoCita::ATENDIDA, EstadoCita::INASISTENCIA, EstadoCita::CANCELADA],
        EstadoCita::ATENDIDA     => [],
        EstadoCita::CANCELADA    => [],
        EstadoCita::INASISTENCIA => [],
    ];

    /**
     * Genera el siguiente código: CM-0001, CM-0002...
     */
    public static function siguienteCodigo(): string
    {
        $ultimo = (int) static::query()
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(codigo FROM 4) AS INTEGER)), 0) AS n')
            ->value('n');

        return 'CM-' . str_pad((string) ($ultimo + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * $cita->horario → "09:00–09:30"
     */
    protected function horario(): Attribute
    {
        return Attribute::get(fn () => substr($this->hora_inicio, 0, 5) . '–' . substr($this->hora_fin, 0, 5));
    }

    /**
     * Fecha y hora en que empieza la cita.
     */
    public function inicio(): Carbon
    {
        return $this->fecha->copy()->setTimeFromTimeString($this->hora_inicio);
    }

    /**
     * ¿Se puede reprogramar o cancelar? (solo Programada o Confirmada)
     */
    public function esModificable(): bool
    {
        return in_array($this->estado->nombre, [EstadoCita::PROGRAMADA, EstadoCita::CONFIRMADA], true);
    }

    /**
     * ¿Está permitida la transición al estado indicado?
     */
    public function puedeCambiarA(string $nuevo): bool
    {
        return in_array($nuevo, self::TRANSICIONES[$this->estado->nombre] ?? [], true);
    }

    // ---- Relaciones ----

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoCita::class, 'estado_cita_id');
    }

    // Usuario que registró la cita
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoCita::class, 'cita_id');
    }

    public function reprogramaciones()
    {
        return $this->hasMany(Reprogramacion::class, 'cita_id');
    }

    // Una cita se cancela como máximo una vez
    public function cancelacion()
    {
        return $this->hasOne(Cancelacion::class, 'cita_id');
    }
}