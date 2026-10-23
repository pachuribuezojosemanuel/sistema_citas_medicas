<?php

namespace App\Http\Middleware;

use App\Models\Bitacora;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermiso
{
    /**
     * Permite el acceso si el usuario tiene al menos uno de los permisos indicados (RN12).
     */
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        $user = $request->user();

        foreach ($permisos as $permiso) {
            if ($user && $user->tienePermiso($permiso)) {
                return $next($request);
            }
        }

        Bitacora::registrar('ACCESO_DENEGADO', 'Seguridad', 'Intento de acceso a: /' . $request->path());

        abort(403);
    }
}