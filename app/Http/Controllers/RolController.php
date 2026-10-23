<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolRequest;
use App\Models\Bitacora;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Gestión de roles y asignación de permisos (RN12).
 */
class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount(['users', 'permisos'])->orderBy('nombre')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create', [
            'rol'           => new Rol(),
            'permisos'      => $this->permisosAgrupados(),
            'seleccionados' => [],
        ]);
    }

    public function store(RolRequest $request)
    {
        // Transacción: el rol y sus permisos se guardan juntos o no se guarda nada
        $rol = DB::transaction(function () use ($request) {
            $rol = Rol::create($request->safe()->only(['nombre', 'descripcion']));
            $rol->permisos()->sync($request->input('permisos', []));

            return $rol;
        });

        Bitacora::registrar('REGISTRAR', 'Roles', "Rol creado: {$rol->nombre}");

        return redirect()->route('roles.index')
            ->with('exito', "Rol {$rol->nombre} registrado correctamente.");
    }

    public function edit(Rol $rol)
    {
        return view('roles.edit', [
            'rol'           => $rol,
            'permisos'      => $this->permisosAgrupados(),
            'seleccionados' => $rol->permisos()->pluck('permisos.id')->all(),
        ]);
    }

    public function update(RolRequest $request, Rol $rol)
    {
        $permisos = array_map('intval', $request->input('permisos', []));

        // Evita que el administrador se quite a sí mismo el acceso a esta pantalla
        if ($this->esRolPropio($rol)) {
            $idGestionar = (int) Permiso::where('codigo', 'ROL_GESTIONAR')->value('id');

            if (! in_array($idGestionar, $permisos, true)) {
                return back()->withInput()
                    ->with('error', 'No puede quitarle a su propio rol el permiso "Gestionar roles y permisos".');
            }
        }

        DB::transaction(function () use ($request, $rol, $permisos) {
            $rol->update($request->safe()->only(['nombre', 'descripcion']));
            $rol->permisos()->sync($permisos);
        });

        Bitacora::registrar('MODIFICAR', 'Roles', "Rol modificado: {$rol->nombre} (" . count($permisos) . ' permisos)');

        return redirect()->route('roles.index')
            ->with('exito', "Rol {$rol->nombre} actualizado correctamente.");
    }

    /**
     * Activa o desactiva un rol. Los usuarios de un rol inactivo no pueden ingresar.
     */
    public function cambiarEstado(Rol $rol)
    {
        if ($this->esRolPropio($rol)) {
            return back()->with('error', 'No puede desactivar el rol con el que inició sesión.');
        }

        $rol->update(['estado' => ! $rol->estado]);

        $accion = $rol->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Roles', "Rol {$rol->nombre}");

        return back()->with('exito', $rol->estado
            ? "Rol {$rol->nombre} activado."
            : "Rol {$rol->nombre} desactivado. Sus usuarios ya no podrán ingresar.");
    }

    /**
     * Permisos ordenados y agrupados por módulo para las casillas.
     */
    private function permisosAgrupados()
    {
        return Permiso::orderBy('modulo')->orderBy('nombre')->get()->groupBy('modulo');
    }

    private function esRolPropio(Rol $rol): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return (int) $user->rol_id === (int) $rol->id;
    }
}