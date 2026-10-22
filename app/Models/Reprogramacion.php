<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reprogramacion extends Model
{
    protected $table = 'reprogramacions';

    public $timestamps = false;

    protected $fillable = [
        'cita_id', 'user_id', 'fecha_anterior', 'hora_anterior',
        'fecha_nueva', 'hora_nueva', 'motivo', 'fecha_registro',
    ];

    protected $casts = [
        'fecha_anterior' => 'date',
        'fecha_nueva'    => 'date',
        'fecha_registro' => 'datetime',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
