<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PacienteAlergia extends Pivot
{
    protected $table = 'paciente_alergia';

    public $timestamps = false;

    protected $fillable = ['paciente_id', 'alergia_id', 'observacion', 'fecha_registro'];

    protected $casts = [
        'fecha_registro' => 'date',
    ];
}
