<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoSangre extends Model
{
    protected $table = 'tipo_sangres';

    protected $fillable = ['nombre'];

    // Un tipo de sangre clasifica a muchos pacientes
    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'tipo_sangre_id');
    }
}
