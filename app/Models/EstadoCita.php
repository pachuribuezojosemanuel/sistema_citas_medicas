<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCita extends Model
{
    protected $table = 'estado_citas';

    protected $fillable = ['nombre', 'descripcion'];

    // Un estado define el estado de muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'estado_cita_id');
    }
}
