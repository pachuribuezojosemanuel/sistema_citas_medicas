<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicoRequest;
use App\Models\Bitacora;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Rol;
use App\Models\User;
use App\Services\DisponibilidadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestión de médicos (RF03). No se eliminan: se activan o desactivan (RN11).
 */
class MedicoController extends Controller
{
    /**
     * Listado con búsqueda por nombre/código/registro y filtro por especialidad.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $medicos = Medico::with('especialidades')
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(function ($q) use ($buscar) {
                    $q->whereRaw("(nombres || ' ' || apellidos) ILIKE ?", ["%{$buscar}%"])
                      ->orWhere('codigo', 'ilike', "%{$buscar}%")
                      ->orWhere('registro_profesional', 'ilike', "%{$buscar}%");
                });
            })
            ->when($request->filled('especialidad_id'), function ($q) use ($request) {
                $q->whereHas('especialidades', fn ($e) => $e->where('especialidades.id', $request->integer('especialidad_id')));
            })
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        $especialidades = Especialidad::orderBy('nombre')->get();

        return view('medicos.index', compact('medicos', 'especialidades', 'buscar'));
    }

    public function create()
    {
        return view('medicos.create', [
            'medico'         => new Medico(),
            'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(),
            'seleccionadas'  => [],
        ]);
    }

    /**
     * Registra el médico y su cuenta de usuario en una sola transacción.
     */
    public function store(MedicoRequest $request)
    {
        $datos = $request->validated();

        $rolMedico = Rol::where('nombre', 'Médico')->first();
        if (! $rolMedico) {
            return back()->withInput()->with('error', 'No existe el rol "Médico". Ejecute el RolSeeder.');
        }

        $medico = DB::transaction(function () use ($datos, $rolMedico) {
            $user = User::create([
                'rol_id'   => $rolMedico->id,
                'name'     => $datos['name'],
                'email'    => $datos['correo'],
                'password' => $datos['password'],   // se cifra con el cast 'hashed'
            ]);

            $medico = Medico::create([
                'user_id'              => $user->id,
                'codigo'               => Medico::siguienteCodigo(),
                'registro_profesional' => $datos['registro_profesional'],
                'nombres'              => $datos['nombres'],
                'apellidos'            => $datos['apellidos'],
                'telefono'             => $datos['telefono'],
                'correo'               => $datos['correo'],
            ]);

            // Especialidades con fecha de asignación
            $hoy = now()->toDateString();
            $medico->especialidades()->attach(
                collect($datos['especialidades'])->mapWithKeys(fn ($id) => [(int) $id => ['fecha_asignacion' => $hoy]])->all()
            );

            return $medico;
        });

        Bitacora::registrar('REGISTRAR', 'Médicos', "Médico creado: {$medico->codigo} - {$medico->nombre_completo}");

        return redirect()->route('medicos.show', $medico)
            ->with('exito', "Médico {$medico->nombre_completo} registrado con el código {$medico->codigo}.");
    }

    /**
     * Ficha del médico: datos, horarios, disponibilidad de un día y próximas citas ("consultar su agenda").
     */
    public function show(Request $request, Medico $medico, DisponibilidadService $disponibilidad)
    {
        $medico->load(['especialidades', 'user', 'horarios' => function ($q) {
            $q->with(['diaSemana', 'consultorio'])->orderBy('dia_semana_id')->orderBy('hora_inicio');
        }]);

        // Fecha a consultar (por defecto hoy)
        try {
            $fecha = Carbon::parse($request->input('fecha', today()->toDateString()))->startOfDay();
        } catch (\Exception $e) {
            $fecha = today();
        }

        $bloques = $disponibilidad->bloquesDelDia($medico, $fecha);

        $proximasCitas = Cita::with(['paciente', 'especialidad', 'estado'])
            ->where('medico_id', $medico->id)
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->limit(20)
            ->get();

        return view('medicos.show', compact('medico', 'fecha', 'bloques', 'proximasCitas'));
    }

    public function edit(Medico $medico)
    {
        $seleccionadas = $medico->especialidades()->pluck('especialidades.id')->all();

        // Activas + las que ya tiene asignadas (aunque estén inactivas)
        $especialidades = Especialidad::where('estado', true)
            ->orWhereIn('id', $seleccionadas)
            ->orderBy('nombre')
            ->get();

        return view('medicos.edit', compact('medico', 'especialidades', 'seleccionadas'));
    }

    public function update(MedicoRequest $request, Medico $medico)
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $medico) {
            $medico->update([
                'registro_profesional' => $datos['registro_profesional'],
                'nombres'              => $datos['nombres'],
                'apellidos'            => $datos['apellidos'],
                'telefono'             => $datos['telefono'],
                'correo'               => $datos['correo'],
            ]);

            // El correo de la cuenta se mantiene igual al del médico
            $medico->user?->update(['email' => $datos['correo']]);

            // Las especialidades que ya tenía conservan su fecha de asignación
            $actuales = $medico->especialidades()->pluck('especialidades.id')->all();
            $hoy      = now()->toDateString();
            $sync     = [];
            foreach ($datos['especialidades'] as $id) {
                $id = (int) $id;
                $sync[$id] = in_array($id, $actuales, true) ? [] : ['fecha_asignacion' => $hoy];
            }
            $medico->especialidades()->sync($sync);
        });

        Bitacora::registrar('MODIFICAR', 'Médicos', "Médico modificado: {$medico->codigo} - {$medico->nombre_completo}");

        return redirect()->route('medicos.show', $medico)
            ->with('exito', 'Datos del médico actualizados correctamente.');
    }

    /**
     * Activa o desactiva. Inactivo = no recibe nuevas citas; sus citas históricas se conservan.
     */
    public function cambiarEstado(Medico $medico)
    {
        $medico->update(['estado' => ! $medico->estado]);

        $accion = $medico->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Médicos', "Médico {$medico->codigo} - {$medico->nombre_completo}");

        return back()->with('exito', $medico->estado
            ? "Médico {$medico->nombre_completo} activado."
            : "Médico {$medico->nombre_completo} desactivado. No podrá recibir nuevas citas; sus citas anteriores se conservan.");
    }
}