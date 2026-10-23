<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Bitacora extends Model
{
    protected $table = 'bitacoras';

    public $timestamps = false;

    protected $fillable = ['user_id', 'accion', 'modulo', 'descripcion', 'ip', 'fecha_hora'];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    /**
     * Registra una acción en la bitácora.
     * Si no se envía $userId, usa el usuario con sesión activa (o null).
     */
    public static function registrar(string $accion, string $modulo, ?string $descripcion = null, ?int $userId = null): void
    {
                self::create([
            'user_id'     => $userId ?? Auth::id(),
            'accion'      => $accion,
            'modulo'      => $modulo,
            'descripcion' => $descripcion,
            'ip'          => request()->ip(),
            'fecha_hora'  => now(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}