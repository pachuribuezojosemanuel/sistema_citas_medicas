<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medicos';

    protected $fillable = [
        'user_id', 'codigo', 'registro_profesional', 'nombres', 'apellidos',
        'telefono', 'correo', 'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Nombre completo: $medico->nombre_completo
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => "{$this->nombres} {$this->apellidos}");
    }

    /**
     * Genera el siguiente código: MED-001, MED-002...
     */
    public static function siguienteCodigo(): string
    {
        $ultimo = (int) static::query()
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(codigo FROM 5) AS INTEGER)), 0) AS n')
            ->value('n');

        return 'MED-' . str_pad((string) ($ultimo + 1), 3, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Cada médico ejerce al menos una especialidad
    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'medico_especialidad', 'medico_id', 'especialidad_id')
                    ->withPivot('fecha_asignacion');
    }

    // Un médico tiene muchos horarios de atención
    public function horarios()
    {
        return $this->hasMany(HorarioAtencion::class, 'medico_id');
    }

    // Un médico atiende muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }
}