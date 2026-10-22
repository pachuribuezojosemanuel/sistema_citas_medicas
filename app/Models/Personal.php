<?php

namespace App\Models;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
