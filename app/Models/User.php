<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['rol_id', 'name', 'email', 'password', 'estado', 'ultimo_acceso'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Un usuario da acceso a 0..1 personal / médico / paciente
    public function personal()
    {
        return $this->hasOne(Personal::class, 'user_id');
    }

    public function medico()
    {
        return $this->hasOne(Medico::class, 'user_id');
    }

    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'user_id');
    }

    // Un usuario genera muchos registros de bitácora
    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'user_id');
    }

    // Citas registradas por el usuario
    public function citasRegistradas()
    {
        return $this->hasMany(Cita::class, 'user_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoCita::class, 'user_id');
    }

    public function reprogramaciones()
    {
        return $this->hasMany(Reprogramacion::class, 'user_id');
    }

    public function cancelaciones()
    {
        return $this->hasMany(Cancelacion::class, 'user_id');
    }
}
