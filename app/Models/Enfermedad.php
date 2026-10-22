<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enfermedad extends Model
{
    protected $table = 'enfermedades';

    protected $fillable = ['nombre', 'descripcion'];

    // Una enfermedad crónica afecta a muchos pacientes
    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_enfermedad', 'enfermedad_id', 'paciente_id')
                    ->withPivot('fecha_diagnostico', 'observacion');
    }
}
