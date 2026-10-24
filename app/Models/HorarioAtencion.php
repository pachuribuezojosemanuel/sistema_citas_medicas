<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HorarioAtencion extends Model
{
    protected $table = 'horario_atencions';

    protected $fillable = [
        'medico_id', 'dia_semana_id', 'consultorio_id',
        'hora_inicio', 'hora_fin', 'duracion_cita', 'estado',
    ];

    protected $casts = [
        'duracion_cita' => 'integer',
        'estado'        => 'boolean',
    ];

    /**
     * Rango legible: "08:00 - 12:00"
     */
    protected function rango(): Attribute
    {
        return Attribute::get(fn () => substr($this->hora_inicio, 0, 5) . ' - ' . substr($this->hora_fin, 0, 5));
    }

    /**
     * Cantidad de citas (bloques) que caben en el horario.
     */
    protected function cantidadBloques(): Attribute
    {
        return Attribute::get(function () {
            [$h1, $m1] = explode(':', $this->hora_inicio);
            [$h2, $m2] = explode(':', $this->hora_fin);
            $minutos = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);

            return intdiv($minutos, $this->duracion_cita);
        });
    }

    /**
     * Busca un horario ACTIVO que se superponga con el rango indicado.
     * $campo = 'medico_id' (mismo médico) o 'consultorio_id' (mismo consultorio).
     */
    public static function buscarChoque(string $campo, int $valor, int $dia, string $inicio, string $fin, ?int $ignorarId = null): ?self
    {
        return static::with(['medico', 'consultorio'])
            ->where($campo, $valor)
            ->where('dia_semana_id', $dia)
            ->where('estado', true)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->where('hora_inicio', '<', $fin)     // empieza antes de que termine el nuevo
            ->where('hora_fin', '>', $inicio)     // y termina después de que empiece el nuevo
            ->first();
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function diaSemana()
    {
        return $this->belongsTo(DiaSemana::class, 'dia_semana_id');
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'consultorio_id');
    }
}