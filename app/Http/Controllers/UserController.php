<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Bitacora;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gestión de usuarios del sistema (solo Administrador).
 * Los usuarios no se eliminan: se activan o desactivan.
 */
class UserController extends Controller
{
    /**
     * Listado con búsqueda y filtros.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $usuarios = User::with('rol')
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(function ($q) use ($buscar) {
                    $q->where('name', 'ilike', "%{$buscar}%")
                      ->orWhere('email', 'ilike', "%{$buscar}%");
                });
            })
            ->when($request->filled('rol_id'), fn ($q) => $q->where('rol_id', $request->integer('rol_id')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $roles = Rol::orderBy('nombre')->get();

        return view('usuarios.index', compact('usuarios', 'roles', 'buscar'));
    }

    public function create()
    {
        return view('usuarios.create', [
            'usuario' => new User(),
            'roles'   => Rol::where('estado', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(UserRequest $request)
    {
        // La contraseña se cifra automáticamente con password_hash (cast 'hashed')
        $usuario = User::create($request->validated());

        Bitacora::registrar('REGISTRAR', 'Usuarios', "Usuario creado: {$usuario->name}");

        return redirect()->route('usuarios.index')
            ->with('exito', "Usuario {$usuario->name} registrado correctamente.");
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles'   => Rol::where('estado', true)->orderBy('nombre')->get(),
        ]);
    }

    public function update(UserRequest $request, User $usuario)
    {
        $datos = $request->validated();

        // Contraseña vacía = se mantiene la actual
        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        // Nadie puede cambiar su propio rol
        if ($usuario->id === Auth::id() && (int) $datos['rol_id'] !== (int) $usuario->rol_id) {
            return back()->withInput()->with('error', 'No puede cambiar su propio rol.');
        }

        $usuario->update($datos);

        $detalle = isset($datos['password']) ? ' (contraseña restablecida)' : '';
        Bitacora::registrar('MODIFICAR', 'Usuarios', "Usuario modificado: {$usuario->name}{$detalle}");

        return redirect()->route('usuarios.index')
            ->with('exito', "Usuario {$usuario->name} actualizado correctamente.");
    }

    /**
     * Activa o desactiva un usuario (no se elimina).
     */
    public function cambiarEstado(User $usuario)
    {
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }

        $usuario->update(['estado' => ! $usuario->estado]);

        $accion = $usuario->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Usuarios', "Usuario {$usuario->name}");

        return back()->with('exito', $usuario->estado
            ? "Usuario {$usuario->name} activado."
            : "Usuario {$usuario->name} desactivado.");
    }
}