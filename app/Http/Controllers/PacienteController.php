<?php

namespace App\Http\Controllers;

use App\Http\Requests\PacienteRequest;
use App\Models\Alergia;
use App\Models\Bitacora;
use App\Models\Cita;
use App\Models\Enfermedad;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Sexo;
use App\Models\TipoSangre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestión de pacientes (RF02). No se eliminan: se desactivan (RN11).
 */
class PacienteController extends Controller
{
    /**
     * Listado con búsqueda por CI, nombre o apellido.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $pacientes = Paciente::query()
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(function ($q) use ($buscar) {
                    $q->where('ci', 'ilike', "%{$buscar}%")
                      ->orWhere('codigo', 'ilike', "%{$buscar}%")
                      ->orWhereRaw("(nombres || ' ' || apellidos) ILIKE ?", ["%{$buscar}%"])
                      ->orWhereRaw("(apellidos || ' ' || nombres) ILIKE ?", ["%{$buscar}%"]);
                });
            })
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'buscar'));
    }

    public function create()
    {
        return view('pacientes.create', $this->datosFormulario(new Paciente()));
    }

    /**
     * Registra el paciente, su información médica, contactos y (opcional) su cuenta.
     */
    public function store(PacienteRequest $request)
    {
        $datos = $request->validated();

        $paciente = DB::transaction(function () use ($datos) {
            $userId = $datos['crear_cuenta'] ? $this->crearCuenta($datos)->id : null;

            $paciente = Paciente::create([
                'user_id'          => $userId,
                'codigo'           => Paciente::siguienteCodigo(),
                ...$this->camposPaciente($datos),
            ]);

            $this->sincronizarRelaciones($paciente, $datos, true);

            return $paciente;
        });

        Bitacora::registrar('REGISTRAR', 'Pacientes', "Paciente creado: {$paciente->codigo} - {$paciente->nombre_completo} (CI {$paciente->ci})");

        return redirect()->route('pacientes.show', $paciente)
            ->with('exito', "Paciente {$paciente->nombre_completo} registrado con el código {$paciente->codigo}.");
    }

    /**
     * Ficha del paciente con su historial de citas (RF02: consultar sus citas anteriores).
     */
    public function show(Paciente $paciente)
    {
        $paciente->load(['sexo', 'tipoSangre', 'alergias', 'enfermedades', 'contactosEmergencia', 'user']);

        $citas = Cita::with(['medico', 'especialidad', 'estado'])
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        return view('pacientes.show', compact('paciente', 'citas'));
    }

    public function edit(Paciente $paciente)
    {
        return view('pacientes.edit', $this->datosFormulario($paciente));
    }

    public function update(PacienteRequest $request, Paciente $paciente)
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $paciente) {
            $campos = $this->camposPaciente($datos);

            // Crear la cuenta si se marcó y todavía no tenía
            if ($datos['crear_cuenta']) {
                $campos['user_id'] = $this->crearCuenta($datos)->id;
            } elseif ($paciente->user) {
                // El correo de la cuenta se mantiene igual al del paciente
                $paciente->user->update(['email' => $datos['correo']]);
            }

            $paciente->update($campos);
            $this->sincronizarRelaciones($paciente, $datos, false);
        });

        Bitacora::registrar('MODIFICAR', 'Pacientes', "Paciente modificado: {$paciente->codigo} - {$paciente->nombre_completo}");

        return redirect()->route('pacientes.show', $paciente)
            ->with('exito', 'Datos del paciente actualizados correctamente.');
    }

    /**
     * Activa o desactiva el paciente (y su cuenta, si tiene). El historial se conserva.
     */
    public function cambiarEstado(Paciente $paciente)
    {
        DB::transaction(function () use ($paciente) {
            $paciente->update(['estado' => ! $paciente->estado]);
            $paciente->user?->update(['estado' => $paciente->estado]);
        });

        $accion = $paciente->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Pacientes', "Paciente {$paciente->codigo} - {$paciente->nombre_completo}");

        return back()->with('exito', $paciente->estado
            ? "Paciente {$paciente->nombre_completo} activado."
            : "Paciente {$paciente->nombre_completo} desactivado. Su historial de citas se conserva.");
    }

    // ------------------------------------------------------------------
    // Métodos de apoyo
    // ------------------------------------------------------------------

    /**
     * Campos propios de la tabla pacientes.
     */
    private function camposPaciente(array $datos): array
    {
        return [
            'ci'               => $datos['ci'],
            'nombres'          => $datos['nombres'],
            'apellidos'        => $datos['apellidos'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'sexo_id'          => $datos['sexo_id'],
            'tipo_sangre_id'   => $datos['tipo_sangre_id'] ?? null,
            'telefono'         => $datos['telefono'],
            'correo'           => $datos['correo'] ?? null,
            'direccion'        => $datos['direccion'] ?? null,
            'medicamentos'     => $datos['medicamentos'] ?? null,
            'antecedentes'     => $datos['antecedentes'] ?? null,
        ];
    }

    /**
     * Crea la cuenta de acceso al portal con rol Paciente.
     */
    private function crearCuenta(array $datos): User
    {
        return User::create([
            'rol_id'   => Rol::where('nombre', 'Paciente')->firstOrFail()->id,
            'name'     => $datos['name'],
            'email'    => $datos['correo'],
            'password' => $datos['password'],   // se cifra con el cast 'hashed'
        ]);
    }

    /**
     * Alergias, enfermedades y contactos de emergencia.
     */
    private function sincronizarRelaciones(Paciente $paciente, array $datos, bool $esNuevo): void
    {
        $hoy = now()->toDateString();

        // Alergias: las que ya tenía conservan su fecha de registro
        $actuales = $esNuevo ? [] : $paciente->alergias()->pluck('alergias.id')->all();
        $sync = [];
        foreach ($datos['alergias'] ?? [] as $id) {
            $id = (int) $id;
            $sync[$id] = ['observacion' => $datos['obs_alergia'][$id] ?? null];
            if (! in_array($id, $actuales, true)) {
                $sync[$id]['fecha_registro'] = $hoy;
            }
        }
        $paciente->alergias()->sync($sync);

        // Enfermedades crónicas
        $sync = [];
        foreach ($datos['enfermedades'] ?? [] as $id) {
            $id = (int) $id;
            $sync[$id] = [
                'fecha_diagnostico' => $datos['diag_enfermedad'][$id] ?? null,
                'observacion'       => $datos['obs_enfermedad'][$id] ?? null,
            ];
        }
        $paciente->enfermedades()->sync($sync);

        // Contactos de emergencia (composición: se reemplazan)
        $paciente->contactosEmergencia()->delete();
        $paciente->contactosEmergencia()->createMany($datos['contactos'] ?? []);
    }

    /**
     * Datos para el formulario de crear / editar.
     */
    private function datosFormulario(Paciente $paciente): array
    {
        if ($paciente->exists) {
            $paciente->load(['alergias', 'enfermedades', 'contactosEmergencia', 'user']);
        }

        $alergiasPaciente     = $paciente->exists ? $paciente->alergias : collect();
        $enfermedadesPaciente = $paciente->exists ? $paciente->enfermedades : collect();
        $contactosPaciente    = $paciente->exists ? $paciente->contactosEmergencia : collect();

        return [
            'paciente'     => $paciente,
            'sexos'        => Sexo::orderBy('nombre')->get(),
            'tiposSangre'  => TipoSangre::orderBy('id')->get(),
            'alergias'     => Alergia::orderBy('nombre')->get(),
            'enfermedades' => Enfermedad::orderBy('nombre')->get(),

            'alergiasMarcadas'     => array_map('intval', old('alergias', $alergiasPaciente->pluck('id')->all())),
            'obsAlergias'          => $alergiasPaciente->pluck('pivot.observacion', 'id')->all(),
            'enfermedadesMarcadas' => array_map('intval', old('enfermedades', $enfermedadesPaciente->pluck('id')->all())),
            'diagEnfermedades'     => $enfermedadesPaciente->pluck('pivot.fecha_diagnostico', 'id')->all(),
            'obsEnfermedades'      => $enfermedadesPaciente->pluck('pivot.observacion', 'id')->all(),

            'contactos' => old('contactos', $contactosPaciente
                ->map(fn ($c) => $c->only(['nombre_completo', 'parentesco', 'telefono']))
                ->all()),
        ];
    }
}