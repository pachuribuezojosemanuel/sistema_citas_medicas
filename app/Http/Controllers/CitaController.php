<?php

namespace App\Http\Controllers;

use App\Exceptions\CitaException;
use App\Http\Requests\CitaRequest;
use App\Http\Requests\ReprogramarRequest;
use App\Models\Bitacora;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use App\Services\CitaService;
use App\Services\DisponibilidadService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Citas médicas: registro (RF06/RF07), estados (RF10),
 * reprogramación (RF08) y cancelación (RF09).
 * Las citas nunca se eliminan.
 */
class CitaController extends Controller
{
    /**
     * Listado: consultar citas por fecha, médico, especialidad y estado.
     */
    public function index(Request $request)
    {
        $request->validate(['fecha' => ['nullable', 'date']]);

        // Sin filtros se muestran las citas de hoy; con la fecha vacía, todas
        $fecha  = $request->has('fecha') ? $request->input('fecha') : today()->toDateString();
        $buscar = trim((string) $request->input('buscar'));
        $like   = "%{$buscar}%";

        $citas = Cita::with(['paciente', 'medico', 'especialidad', 'estado'])
            ->when($fecha, fn ($q) => $q->whereDate('fecha', $fecha))
            ->when($request->filled('medico_id'), fn ($q) => $q->where('medico_id', $request->integer('medico_id')))
            ->when($request->filled('especialidad_id'), fn ($q) => $q->where('especialidad_id', $request->integer('especialidad_id')))
            ->when($request->filled('estado_cita_id'), fn ($q) => $q->where('estado_cita_id', $request->integer('estado_cita_id')))
            ->when($buscar !== '', function ($consulta) use ($like) {
                $consulta->where(function ($q) use ($like) {
                    $q->where('codigo', 'ilike', $like)
                      ->orWhereHas('paciente', fn ($p) => $p->where('ci', 'ilike', $like)
                          ->orWhereRaw("(nombres || ' ' || apellidos) ILIKE ?", [$like]));
                });
            })
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('citas.index', [
            'citas'          => $citas,
            'fecha'          => $fecha,
            'buscar'         => $buscar,
            'medicos'        => Medico::orderBy('apellidos')->orderBy('nombres')->get(),
            'especialidades' => Especialidad::orderBy('nombre')->get(),
            'estados'        => EstadoCita::orderBy('id')->get(),
        ]);
    }

    /**
     * Formulario de registro (RF06).
     */
    public function create(Request $request)
    {
        return view('citas.create', [
            'pacientes'      => Paciente::where('estado', true)->orderBy('apellidos')->orderBy('nombres')
                                    ->get(['id', 'codigo', 'ci', 'nombres', 'apellidos']),
            'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(),
            'pacienteId'     => old('paciente_id', $request->integer('paciente_id') ?: null),
            'modalidades'    => Cita::MODALIDADES,
        ]);
    }

    public function store(CitaRequest $request, CitaService $servicio)
    {
        try {
            $cita = $servicio->registrar($request->validated(), $this->usuario());
        } catch (CitaException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->mensajeBaseDatos($e));
        }

        $cita->load(['paciente', 'medico']);

        Bitacora::registrar('REGISTRAR', 'Citas',
            "Cita {$cita->codigo}: {$cita->paciente->nombre_completo} con {$cita->medico->nombre_completo}, " .
            "{$cita->fecha->format('d/m/Y')} {$cita->horario}");

        return redirect()->route('citas.show', $cita)
            ->with('exito', "Cita {$cita->codigo} registrada para el {$cita->fecha->format('d/m/Y')} de {$cita->horario}.");
    }

    /**
     * Detalle de la cita con su historial, reprogramaciones y cancelación.
     */
    public function show(Cita $cita)
    {
        $this->autorizarCita($cita);

        $cita->load([
            'paciente.alergias', 'medico', 'especialidad', 'estado', 'user',
            'historialEstados' => fn ($q) => $q->with(['estadoAnterior', 'estadoNuevo', 'user'])->orderBy('fecha_cambio')->orderBy('id'),
            'reprogramaciones' => fn ($q) => $q->with('user')->orderBy('fecha_registro'),
            'cancelacion.user',
        ]);

        return view('citas.show', compact('cita'));
    }

    // ------------------------------------------------------------------
    // RF10 — Estados y asistencia
    // ------------------------------------------------------------------

    public function cambiarEstado(Request $request, Cita $cita, CitaService $servicio)
    {
        $datos = $request->validate([
            'estado'      => ['required', Rule::in([EstadoCita::CONFIRMADA, EstadoCita::ATENDIDA, EstadoCita::INASISTENCIA])],
            'observacion' => ['nullable', 'string', 'max:200'],
        ]);

        $this->autorizarCita($cita);

        // Permiso según el estado destino (sección 3.2 y 3.3 del examen)
        $usuario  = $this->usuario();
        $permisos = $datos['estado'] === EstadoCita::ATENDIDA ? ['CITA_ATENDER', 'CITA_ASISTENCIA'] : ['CITA_ASISTENCIA'];
        if (! collect($permisos)->contains(fn ($p) => $usuario->tienePermiso($p))) {
            abort(403);
        }

        try {
            [$anterior, $nuevo] = $servicio->cambiarEstado($cita, $datos['estado'], $datos['observacion'] ?? null, $usuario);
        } catch (CitaException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            return back()->with('error', $this->mensajeBaseDatos($e));
        }

        Bitacora::registrar('CAMBIAR_ESTADO', 'Citas', "Cita {$cita->codigo}: {$anterior} → {$nuevo}");

        return back()->with('exito', "Cita {$cita->codigo} marcada como {$nuevo}.");
    }

    // ------------------------------------------------------------------
    // RF08 — Reprogramación
    // ------------------------------------------------------------------

    public function reprogramarForm(Cita $cita)
    {
        $cita->load(['paciente', 'medico', 'especialidad', 'estado']);

        if (! $cita->esModificable()) {
            return redirect()->route('citas.show', $cita)->with('error',
                "Una cita {$cita->estado->nombre} no puede reprogramarse. Registre una nueva cita.");
        }

        return view('citas.reprogramar', compact('cita'));
    }

    public function reprogramar(ReprogramarRequest $request, Cita $cita, CitaService $servicio)
    {
        $anterior = $cita->fecha->format('d/m/Y') . ' ' . $cita->horario;

        try {
            $cita = $servicio->reprogramar($cita, $request->validated(), $this->usuario());
        } catch (CitaException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->mensajeBaseDatos($e));
        }

        $nueva = $cita->fecha->format('d/m/Y') . ' ' . $cita->horario;
        Bitacora::registrar('REPROGRAMAR', 'Citas', "Cita {$cita->codigo}: {$anterior} → {$nueva}");

        return redirect()->route('citas.show', $cita)
            ->with('exito', "Cita {$cita->codigo} reprogramada para el {$nueva}.");
    }

    // ------------------------------------------------------------------
    // RF09 — Cancelación
    // ------------------------------------------------------------------

    public function cancelar(Request $request, Cita $cita, CitaService $servicio)
    {
        $datos = $request->validate(
            ['motivo' => ['required', 'string', 'min:5', 'max:200']],
            [
                'motivo.required' => 'Indique el motivo de la cancelación.',
                'motivo.min'      => 'Describa el motivo con al menos 5 caracteres.',
            ]
        );

        try {
            $servicio->cancelar($cita, trim($datos['motivo']), $this->usuario());
        } catch (CitaException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            return back()->with('error', $this->mensajeBaseDatos($e));
        }

        Bitacora::registrar('CANCELAR', 'Citas', "Cita {$cita->codigo} cancelada: {$datos['motivo']}");

        return redirect()->route('citas.show', $cita)
            ->with('exito', "Cita {$cita->codigo} cancelada. El horario quedó libre y la cita se conserva en el historial.");
    }

    // ------------------------------------------------------------------
    // Consultas para el formulario (Fetch API, devuelven JSON)
    // ------------------------------------------------------------------

    /**
     * Médicos activos que atienden una especialidad.
     */
    public function medicosPorEspecialidad(Request $request)
    {
        $request->validate(['especialidad_id' => ['required', 'integer']]);

        $medicos = Medico::where('estado', true)
            ->whereHas('especialidades', fn ($q) => $q->where('especialidades.id', $request->integer('especialidad_id')))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return response()->json($medicos->map(fn ($m) => [
            'id'     => $m->id,
            'nombre' => "{$m->apellidos}, {$m->nombres} ({$m->codigo})",
        ]));
    }

    /**
     * Horarios libres de un médico en una fecha (RF06: "solo los horarios disponibles").
     */
    public function disponibilidad(Request $request, DisponibilidadService $servicio)
    {
        $datos = $request->validate([
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
            'fecha'     => ['required', 'date_format:Y-m-d'],
            'ignorar'   => ['nullable', 'integer'],     // al reprogramar, la propia cita
        ]);

        $medico = Medico::findOrFail($datos['medico_id']);
        $fecha  = Carbon::parse($datos['fecha'])->startOfDay();

        return response()->json([
            'fecha'   => $fecha->translatedFormat('l d/m/Y'),
            'bloques' => $servicio->bloquesLibres($medico, $fecha, $datos['ignorar'] ?? null),
        ]);
    }

    // ------------------------------------------------------------------
    // Apoyo
    // ------------------------------------------------------------------

    private function usuario(): User
    {
        /** @var User $usuario */
        $usuario = Auth::user();

        return $usuario;
    }

    /**
     * Personal con CITA_VER ve todas; el médico solo las suyas (se usará en "Mi agenda").
     */
    private function autorizarCita(Cita $cita): void
    {
        $usuario = $this->usuario();

        if ($usuario->tienePermiso('CITA_VER')) {
            return;
        }

        if ($usuario->medico && $cita->medico_id === $usuario->medico->id) {
            return;
        }

        abort(403);
    }

    /**
     * Si un trigger de PostgreSQL rechaza la operación (Parte F), muestra su mensaje;
     * cualquier otro error se muestra de forma genérica, sin detalles internos.
     */
    private function mensajeBaseDatos(QueryException $e): string
    {
        if (($e->errorInfo[0] ?? null) === 'P0001' && preg_match('/ERROR:\s+(.+?)(\n|$)/', $e->getMessage(), $m)) {
            return $m[1];
        }

        report($e);

        return 'No se pudo completar la operación. Intente nuevamente.';
    }
}