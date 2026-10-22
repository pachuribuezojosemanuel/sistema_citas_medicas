<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoCita extends Model
{
    protected $table = 'historial_estado_citas';

    public $timestamps = false;

    protected $fillable = [
        'cita_id', 'estado_anterior_id', 'estado_nuevo_id', 'user_id', 'fecha_cambio', 'observacion',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function estadoAnterior()
    {
        return $this->belongsTo(EstadoCita::class, 'estado_anterior_id');
    }

    public function estadoNuevo()
    {
        return $this->belongsTo(EstadoCita::class, 'estado_nuevo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
