<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacoras';

    public $timestamps = false;

    protected $fillable = ['user_id', 'accion', 'modulo', 'descripcion', 'ip', 'fecha_hora'];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public static function registrar(string $accion, string $modulo, ?string $descripcion = null, ?int $userId = null): void
    {
        self::create([
            'user_id'     => $userId ?? auth()->id(),
            'accion'      => $accion,
            'modulo'      => $modulo,
            'descripcion' => $descripcion,
            'ip'          => request()->ip(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
