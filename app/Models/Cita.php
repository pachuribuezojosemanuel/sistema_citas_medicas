<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'codigo', 'paciente_id', 'medico_id', 'especialidad_id', 'estado_cita_id', 'user_id',
        'fecha', 'hora_inicio', 'hora_fin', 'motivo', 'modalidad',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoCita::class, 'estado_cita_id');
    }

    // Usuario que registró la cita
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoCita::class, 'cita_id');
    }

    public function reprogramaciones()
    {
        return $this->hasMany(Reprogramacion::class, 'cita_id');
    }

    // Una cita se cancela como máximo una vez
    public function cancelacion()
    {
        return $this->hasOne(Cancelacion::class, 'cita_id');
    }
}
