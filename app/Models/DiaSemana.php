<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaSemana extends Model
{
    protected $table = 'dia_semanas';

    protected $fillable = ['nombre'];

    // Un día de la semana define muchos horarios
    public function horarios()
    {
        return $this->hasMany(HorarioAtencion::class, 'dia_semana_id');
    }
}
