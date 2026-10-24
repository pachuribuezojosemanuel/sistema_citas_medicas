<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Especialidad extends Model
{
    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Nombre normalizado para comparar duplicados (RF04):
     * sin espacios sobrantes, en minúsculas y sin tildes.
     */
    public static function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii(preg_replace('/\s+/', ' ', trim($texto))));
    }

    /**
     * Nombre con formato para guardar: espacios limpios y primera letra en mayúscula.
     * Ej.: "  medicina   general " → "Medicina general"
     */
    public static function formatear(string $texto): string
    {
        $texto = preg_replace('/\s+/', ' ', trim($texto));

        return mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
    }

    // Una especialidad es ejercida por muchos médicos
    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'medico_especialidad', 'especialidad_id', 'medico_id')
                    ->withPivot('fecha_asignacion');
    }

    // Una especialidad clasifica muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'especialidad_id');
    }
}