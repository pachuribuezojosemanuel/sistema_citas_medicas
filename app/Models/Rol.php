<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rols';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Un rol es asignado a muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    // Un rol tiene muchos permisos
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id')
                    ->withPivot('fecha_asignacion');
    }
}
