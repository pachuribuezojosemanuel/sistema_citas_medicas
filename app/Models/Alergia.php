<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alergia extends Model
{
    protected $table = 'alergias';

    protected $fillable = ['nombre', 'descripcion'];

    // Una alergia afecta a muchos pacientes
    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_alergia', 'alergia_id', 'paciente_id')
            ->withPivot('observacion', 'fecha_registro');
    }
}
