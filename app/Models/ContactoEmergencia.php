<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoEmergencia extends Model
{
    protected $table = 'contacto_emergencias';

    protected $fillable = ['paciente_id', 'nombre_completo', 'parentesco', 'telefono'];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
