<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RolPermiso extends Pivot
{
    protected $table = 'rol_permiso';

    public $timestamps = false;

    protected $fillable = ['rol_id', 'permiso_id', 'fecha_asignacion'];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];
}
