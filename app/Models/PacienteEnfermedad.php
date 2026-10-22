<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PacienteEnfermedad extends Pivot
{
    protected $table = 'paciente_enfermedad';

    public $timestamps = false;

    protected $fillable = ['paciente_id', 'enfermedad_id', 'fecha_diagnostico', 'observacion'];

    protected $casts = [
        'fecha_diagnostico' => 'date',
    ];
}
