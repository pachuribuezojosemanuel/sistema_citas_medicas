<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MedicoEspecialidad extends Pivot
{
    protected $table = 'medico_especialidad';

    public $timestamps = false;

    protected $fillable = ['medico_id', 'especialidad_id', 'fecha_asignacion'];

    protected $casts = [
        'fecha_asignacion' => 'date',
    ];
}
