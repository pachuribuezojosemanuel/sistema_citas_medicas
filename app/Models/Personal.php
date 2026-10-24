<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personals';

    protected $fillable = [
        'user_id', 'ci', 'nombres', 'apellidos', 'telefono', 'cargo', 'fecha_ingreso', 'estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'estado'        => 'boolean',
    ];

    /**
     * $personal->nombre_completo
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => "{$this->nombres} {$this->apellidos}");
    }

    // Usuario que da acceso a este miembro del personal
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}