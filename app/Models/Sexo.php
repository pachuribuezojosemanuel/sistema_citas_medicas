<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    protected $table = 'sexos';

    protected $fillable = ['nombre'];

    // Un sexo clasifica a muchos pacientes
    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'sexo_id');
    }
}
