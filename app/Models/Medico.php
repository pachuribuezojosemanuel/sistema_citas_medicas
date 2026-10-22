<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medicos';

    protected $fillable = [
        'user_id', 'codigo', 'registro_profesional', 'nombres', 'apellidos',
        'telefono', 'correo', 'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Cada médico ejerce al menos una especialidad
    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'medico_especialidad', 'medico_id', 'especialidad_id')
                    ->withPivot('fecha_asignacion');
    }

    // Un médico tiene muchos horarios de atención
    public function horarios()
    {
        return $this->hasMany(HorarioAtencion::class, 'medico_id');
    }

    // Un médico atiende muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }
}
