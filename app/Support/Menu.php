<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Construye el menú filtrado según los permisos del usuario (RF01).
 */
class Menu
{
    public static function paraUsuario(User $user): array
    {
        $menu = [];

        foreach (config('menu') as $entrada) {
            // Submenú desplegable
            if (isset($entrada['items'])) {
                $items = [];

                foreach ($entrada['items'] as $item) {
                    if (self::esVisible($item, $user)) {
                        $item['activo'] = self::estaActivo($item['ruta']);
                        $items[] = $item;
                    }
                }

                // Solo se muestra el grupo si tiene al menos una opción
                if (count($items) > 0) {
                    $menu[] = [
                        'texto'  => $entrada['texto'],
                        'items'  => $items,
                        'activo' => collect($items)->contains('activo', true),
                    ];
                }

                continue;
            }

            // Enlace simple
            if (self::esVisible($entrada, $user)) {
                $entrada['activo'] = self::estaActivo($entrada['ruta']);
                $menu[] = $entrada;
            }
        }

        return $menu;
    }

    /**
     * Visible si la ruta existe y el usuario tiene el permiso.
     */
    private static function esVisible(array $item, User $user): bool
    {
        if (! Route::has($item['ruta'])) {
            return false;
        }

        return empty($item['permiso']) || $user->tienePermiso($item['permiso']);
    }

    /**
     * Marca como activo el módulo actual (ej. pacientes.index, pacientes.create...).
     */
    private static function estaActivo(string $ruta): bool
    {
        $patron = Str::contains($ruta, '.') ? Str::beforeLast($ruta, '.') . '.*' : $ruta;

        return request()->routeIs($patron);
    }
}