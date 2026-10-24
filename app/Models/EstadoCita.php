<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCita extends Model
{
    protected $table = 'estado_citas';

    protected $fillable = ['nombre', 'descripcion'];

    // Estados de la cita (RF10)
    public const PROGRAMADA   = 'Programada';
    public const CONFIRMADA   = 'Confirmada';
    public const ATENDIDA     = 'Atendida';
    public const CANCELADA    = 'Cancelada';
    public const INASISTENCIA = 'Inasistencia';

    /**
     * Devuelve el id de un estado por su nombre. Ej.: EstadoCita::idDe(EstadoCita::PROGRAMADA)
     */
    public static function idDe(string $nombre): int
    {
        return (int) static::where('nombre', $nombre)->value('id');
    }

    // Un estado define el estado de muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'estado_cita_id');
    }
}