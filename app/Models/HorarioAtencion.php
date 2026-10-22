<?php

namespace App\Models;

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
