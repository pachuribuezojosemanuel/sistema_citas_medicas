<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    protected $table = 'consultorios';

    protected $fillable = ['numero', 'nombre', 'piso', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Un consultorio aloja muchos horarios de atención
    public function horarios()
    {
        return $this->hasMany(HorarioAtencion::class, 'consultorio_id');
    }
}
