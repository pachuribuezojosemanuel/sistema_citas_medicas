<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaSemana extends Model
{
    protected $table = 'dia_semanas';

    protected $fillable = ['nombre'];

    /*
    | Los ids siguen la norma ISO 8601: 1 = Lunes ... 7 = Domingo.
    | Coincide con Carbon: $fecha->dayOfWeekIso devuelve el mismo número,
    | así se sabe directamente qué horario corresponde a una fecha.
    */

    // Un día de la semana define muchos horarios
    public function horarios()
    {
        return $this->hasMany(HorarioAtencion::class, 'dia_semana_id');
    }
}