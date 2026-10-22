<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'user_id', 'sexo_id', 'tipo_sangre_id', 'codigo', 'ci', 'nombres', 'apellidos',
        'fecha_nacimiento', 'telefono', 'correo', 'direccion', 'medicamentos',
        'antecedentes', 'estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'estado'           => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sexo()
    {
        return $this->belongsTo(Sexo::class, 'sexo_id');
    }

    public function tipoSangre()
    {
        return $this->belongsTo(TipoSangre::class, 'tipo_sangre_id');
    }

    public function contactosEmergencia()
    {
        return $this->hasMany(ContactoEmergencia::class, 'paciente_id');
    }

    public function alergias()
    {
        return $this->belongsToMany(Alergia::class, 'paciente_alergia', 'paciente_id', 'alergia_id')
                    ->withPivot('observacion', 'fecha_registro');
    }

    public function enfermedades()
    {
        return $this->belongsToMany(Enfermedad::class, 'paciente_enfermedad', 'paciente_id', 'enfermedad_id')
                    ->withPivot('fecha_diagnostico', 'observacion');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }
}
