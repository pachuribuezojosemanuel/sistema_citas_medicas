<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Una especialidad es ejercida por muchos médicos
    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'medico_especialidad', 'especialidad_id', 'medico_id')
                    ->withPivot('fecha_asignacion');
    }

    // Una especialidad clasifica muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'especialidad_id');
    }
}
