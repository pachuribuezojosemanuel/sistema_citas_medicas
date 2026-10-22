<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancelacion extends Model
{
    protected $table = 'cancelacions';

    public $timestamps = false;

    protected $fillable = ['cita_id', 'user_id', 'motivo', 'fecha_cancelacion'];

    protected $casts = [
        'fecha_cancelacion' => 'datetime',
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
