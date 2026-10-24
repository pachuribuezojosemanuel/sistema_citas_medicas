<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Bitacora;
use App\Models\Personal;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Gestión de usuarios y personal administrativo (solo Administrador).
 * No se eliminan: se activan o desactivan.
 */
class UserController extends Controller
{
    private const CAMPOS_USUARIO  = ['name', 'email', 'rol_id', 'password'];
    private const CAMPOS_PERSONAL = ['ci', 'nombres', 'apellidos', 'telefono', 'cargo', 'fecha_ingreso'];

    /**
     * Listado con búsqueda por usuario, correo, nombre o CI.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));
        $like   = "%{$buscar}%";
        $nombre = "(nombres || ' ' || apellidos) ILIKE ?";

        $usuarios = User::with(['rol', 'personal', 'medico', 'paciente'])
            ->when($buscar !== '', function ($consulta) use ($like, $nombre) {
                $consulta->where(function ($q) use ($like, $nombre) {
                    $q->where('name', 'ilike', $like)
                      ->orWhere('email', 'ilike', $like)
                      ->orWhereHas('personal', fn ($p) => $p->where('ci', 'ilike', $like)->orWhereRaw($nombre, [$like]))
                      ->orWhereHas('medico',   fn ($m) => $m->whereRaw($nombre, [$like]))
                      ->orWhereHas('paciente', fn ($p) => $p->where('ci', 'ilike', $like)->orWhereRaw($nombre, [$like]));
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
            'usuario'   => new User(),
            'personal'  => new Personal(),
            'roles'     => $this->rolesDePersonal(),
            'vinculo'   => null,
        ]);
    }

    /**
     * Crea el usuario y su registro de personal en una sola transacción.
     */
    public function store(UserRequest $request)
    {
        $datos = $request->validated();

        $usuario = DB::transaction(function () use ($datos) {
            // La contraseña se cifra automáticamente con password_hash (cast 'hashed')
            $usuario = User::create(Arr::only($datos, self::CAMPOS_USUARIO));
            $usuario->personal()->create(Arr::only($datos, self::CAMPOS_PERSONAL));

            return $usuario;
        });

        Bitacora::registrar('REGISTRAR', 'Usuarios',
            "Usuario creado: {$usuario->name} ({$datos['nombres']} {$datos['apellidos']}, CI {$datos['ci']})");

        return redirect()->route('usuarios.index')
            ->with('exito', "Usuario {$usuario->name} registrado correctamente.");
    }

    public function edit(User $usuario)
    {
        $usuario->load(['rol', 'personal', 'medico', 'paciente']);

        return view('usuarios.edit', [
            'usuario'  => $usuario,
            'personal' => $usuario->personal ?? new Personal(),
            'roles'    => $this->rolesDePersonal(),
            'vinculo'  => $this->vinculo($usuario),
        ]);
    }

    public function update(UserRequest $request, User $usuario)
    {
        $datos   = $request->validated();
        $vinculo = $this->vinculo($usuario);

        // Contraseña vacía = se mantiene la actual
        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        // Nadie puede cambiar su propio rol
        if (! $vinculo && $usuario->id === Auth::id() && (int) $datos['rol_id'] !== (int) $usuario->rol_id) {
            return back()->withInput()->with('error', 'No puede cambiar su propio rol.');
        }

        DB::transaction(function () use ($datos, $usuario, $vinculo) {
            $usuario->update(Arr::only($datos, self::CAMPOS_USUARIO));

            // Personal: se actualiza o se crea si el usuario aún no tenía sus datos
            if (! $vinculo) {
                $usuario->personal()->updateOrCreate([], Arr::only($datos, self::CAMPOS_PERSONAL));
            }
        });

        $detalle = isset($datos['password']) ? ' (contraseña restablecida)' : '';
        Bitacora::registrar('MODIFICAR', 'Usuarios', "Usuario modificado: {$usuario->name}{$detalle}");

        return redirect()->route('usuarios.index')
            ->with('exito', "Usuario {$usuario->name} actualizado correctamente.");
    }

    /**
     * Activa o desactiva el usuario y su registro de personal.
     */
    public function cambiarEstado(User $usuario)
    {
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }

        DB::transaction(function () use ($usuario) {
            $usuario->update(['estado' => ! $usuario->estado]);
            $usuario->personal?->update(['estado' => $usuario->estado]);
        });

        $accion = $usuario->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Usuarios', "Usuario {$usuario->name}");

        return back()->with('exito', $usuario->estado
            ? "Usuario {$usuario->name} activado."
            : "Usuario {$usuario->name} desactivado.");
    }

    // ------------------------------------------------------------------

    /**
     * Roles activos que corresponden a personal administrativo.
     */
    private function rolesDePersonal()
    {
        return Rol::where('estado', true)
            ->whereNotIn('nombre', Rol::ROLES_CON_MODULO)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Si la cuenta pertenece a un médico o paciente, devuelve a cuál y dónde se editan sus datos.
     */
    private function vinculo(User $usuario): ?array
    {
        if ($usuario->medico) {
            return [
                'tipo'   => 'médico',
                'nombre' => $usuario->medico->nombre_completo,
                'url'    => Route::has('medicos.edit') ? route('medicos.edit', $usuario->medico) : null,
                'modulo' => 'Médicos',
            ];
        }

        if ($usuario->paciente) {
            return [
                'tipo'   => 'paciente',
                'nombre' => $usuario->paciente->nombre_completo,
                'url'    => Route::has('pacientes.edit') ? route('pacientes.edit', $usuario->paciente) : null,
                'modulo' => 'Pacientes',
            ];
        }

        return null;
    }
}