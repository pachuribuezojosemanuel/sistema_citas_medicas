<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = ['codigo', 'nombre', 'modulo', 'descripcion'];

    // Un permiso es otorgado a muchos roles
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'permiso_id', 'rol_id')
                    ->withPivot('fecha_asignacion');
    }
}
