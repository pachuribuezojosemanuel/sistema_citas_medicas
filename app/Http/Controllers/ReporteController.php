<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reportes del sistema (RF13). Todos permiten filtrar por fecha o rango,
 * muestran totales y se pueden exportar a CSV o imprimir / guardar como PDF.
 */
class ReporteController extends Controller
{
    /** Conteos por estado reutilizados en los resúmenes (PostgreSQL: COUNT ... FILTER) */
    private const CONTEOS = "
        COUNT(*) AS total,
        COUNT(*) FILTER (WHERE e.nombre IN ('Programada', 'Confirmada')) AS pendientes,
        COUNT(*) FILTER (WHERE e.nombre = 'Atendida')     AS atendidas,
        COUNT(*) FILTER (WHERE e.nombre = 'Cancelada')    AS canceladas,
        COUNT(*) FILTER (WHERE e.nombre = 'Inasistencia') AS inasistencias";

    /**
     * Menú de reportes.
     */
    public function index()
    {
        return view('reportes.index');
    }

    // ==================================================================
    // 1. Listado de citas (por fecha, médico, especialidad y estado)
    // ==================================================================

    public function citas(Request $request)
    {
        [$desde, $hasta] = $this->rango($request, today(), today());

        $consulta = Cita::with(['paciente', 'medico', 'especialidad', 'estado'])
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($request->filled('medico_id'), fn ($q) => $q->where('medico_id', $request->integer('medico_id')))
            ->when($request->filled('especialidad_id'), fn ($q) => $q->where('especialidad_id', $request->integer('especialidad_id')))
            ->when($request->filled('estado_cita_id'), fn ($q) => $q->where('estado_cita_id', $request->integer('estado_cita_id')))
            ->orderBy('fecha')
            ->orderBy('hora_inicio');

        $citas = $consulta->get();

        if ($request->input('exportar') === 'csv') {
            Bitacora::registrar('EXPORTAR', 'Reportes', "Listado de citas {$desde->format('d/m/Y')} - {$hasta->format('d/m/Y')} ({$citas->count()} filas)");

            return $this->csv('reporte_citas',
                ['Fecha', 'Hora', 'Código', 'CI', 'Paciente', 'Médico', 'Especialidad', 'Modalidad', 'Estado', 'Motivo'],
                $citas->map(fn ($c) => [
                    $c->fecha->format('d/m/Y'), $c->horario, $c->codigo, $c->paciente->ci, $c->paciente->nombre_completo,
                    $c->medico->nombre_completo, $c->especialidad->nombre, $c->modalidad, $c->estado->nombre, $c->motivo,
                ])
            );
        }

        return view('reportes.citas', [
            'citas'          => $citas,
            'totales'        => $citas->countBy(fn ($c) => $c->estado->nombre),
            'desde'          => $desde,
            'hasta'          => $hasta,
            'medicos'        => Medico::orderBy('apellidos')->orderBy('nombres')->get(),
            'especialidades' => Especialidad::orderBy('nombre')->get(),
            'estados'        => EstadoCita::orderBy('id')->get(),
        ]);
    }

    // ==================================================================
    // 2. Resumen del período: por estado, por médico y por especialidad
    // ==================================================================

    public function resumen(Request $request)
    {
        [$desde, $hasta] = $this->rango($request, today()->startOfMonth(), today()->endOfMonth());
        $periodo = [$desde->toDateString(), $hasta->toDateString()];

        $base = fn () => DB::table('citas as c')
            ->join('estado_citas as e', 'e.id', '=', 'c.estado_cita_id')
            ->whereBetween('c.fecha', $periodo);

        $general = $base()->selectRaw(self::CONTEOS)->first();

        $porMedico = $base()
            ->join('medicos as m', 'm.id', '=', 'c.medico_id')
            ->groupBy('m.id', 'm.nombres', 'm.apellidos')
            ->selectRaw("m.nombres || ' ' || m.apellidos AS nombre, " . self::CONTEOS)
            ->orderByDesc('total')
            ->get();

        $porEspecialidad = $base()
            ->join('especialidades as s', 's.id', '=', 'c.especialidad_id')
            ->groupBy('s.id', 's.nombre')
            ->selectRaw('s.nombre AS nombre, ' . self::CONTEOS)
            ->orderByDesc('total')
            ->get();

        if ($request->input('exportar') === 'csv') {
            Bitacora::registrar('EXPORTAR', 'Reportes', "Resumen {$desde->format('d/m/Y')} - {$hasta->format('d/m/Y')}");

            $filas = [['GENERAL', '', $general->total, $general->pendientes, $general->atendidas, $general->canceladas, $general->inasistencias]];
            foreach ($porMedico as $f) {
                $filas[] = ['Médico', $f->nombre, $f->total, $f->pendientes, $f->atendidas, $f->canceladas, $f->inasistencias];
            }
            foreach ($porEspecialidad as $f) {
                $filas[] = ['Especialidad', $f->nombre, $f->total, $f->pendientes, $f->atendidas, $f->canceladas, $f->inasistencias];
            }

            return $this->csv('reporte_resumen',
                ['Agrupado por', 'Nombre', 'Total', 'Pendientes', 'Atendidas', 'Canceladas', 'Inasistencias'], $filas);
        }

        return view('reportes.resumen', compact('general', 'porMedico', 'porEspecialidad', 'desde', 'hasta'));
    }

    // ==================================================================
    // 3. Pacientes registrados
    // ==================================================================

    public function pacientes(Request $request)
    {
        [$desde, $hasta] = $this->rango($request, today()->startOfMonth(), today());

        $pacientes = Paciente::with('sexo')
            ->withCount('citas')
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->orderBy('created_at')
            ->get();

        if ($request->input('exportar') === 'csv') {
            Bitacora::registrar('EXPORTAR', 'Reportes', "Pacientes registrados {$desde->format('d/m/Y')} - {$hasta->format('d/m/Y')}");

            return $this->csv('reporte_pacientes',
                ['Código', 'CI', 'Paciente', 'Edad', 'Sexo', 'Teléfono', 'Registrado', 'Citas', 'Estado'],
                $pacientes->map(fn ($p) => [
                    $p->codigo, $p->ci, $p->nombre_completo, $p->edad, $p->sexo->nombre, $p->telefono,
                    $p->created_at->format('d/m/Y'), $p->citas_count, $p->estado ? 'Activo' : 'Inactivo',
                ])
            );
        }

        return view('reportes.pacientes', compact('pacientes', 'desde', 'hasta'));
    }

    // ==================================================================
    // 4. Agenda diaria de cada médico
    // ==================================================================

    public function agenda(Request $request)
    {
        $request->validate([
            'fecha'     => ['nullable', 'date_format:Y-m-d'],
            'medico_id' => ['nullable', 'integer'],
        ]);

        $fecha = Carbon::parse($request->input('fecha', today()->toDateString()))->startOfDay();

        $citas = Cita::with(['paciente', 'medico', 'especialidad', 'estado'])
            ->whereDate('fecha', $fecha)
            ->when($request->filled('medico_id'), fn ($q) => $q->where('medico_id', $request->integer('medico_id')))
            ->orderBy('hora_inicio')
            ->get();

        $porMedico = $citas->groupBy('medico_id')->sortBy(fn ($grupo) => $grupo->first()->medico->apellidos);

        if ($request->input('exportar') === 'csv') {
            Bitacora::registrar('EXPORTAR', 'Reportes', "Agenda diaria {$fecha->format('d/m/Y')}");

            return $this->csv('agenda_' . $fecha->format('Y_m_d'),
                ['Médico', 'Hora', 'Código', 'CI', 'Paciente', 'Especialidad', 'Estado', 'Motivo'],
                $citas->map(fn ($c) => [
                    $c->medico->nombre_completo, $c->horario, $c->codigo, $c->paciente->ci, $c->paciente->nombre_completo,
                    $c->especialidad->nombre, $c->estado->nombre, $c->motivo,
                ])
            );
        }

        return view('reportes.agenda', [
            'fecha'     => $fecha,
            'porMedico' => $porMedico,
            'total'     => $citas->count(),
            'medicos'   => Medico::orderBy('apellidos')->orderBy('nombres')->get(),
        ]);
    }

    // ------------------------------------------------------------------
    // Apoyo
    // ------------------------------------------------------------------

    /**
     * Valida el rango de fechas (máximo un año) y aplica valores por defecto.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rango(Request $request, Carbon $desdeDefecto, Carbon $hastaDefecto): array
    {
        $request->validate([
            'desde'           => ['nullable', 'date_format:Y-m-d'],
            'hasta'           => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'medico_id'       => ['nullable', 'integer'],
            'especialidad_id' => ['nullable', 'integer'],
            'estado_cita_id'  => ['nullable', 'integer'],
        ], [
            'hasta.after_or_equal' => 'La fecha "hasta" debe ser igual o posterior a "desde".',
        ]);

        $desde = Carbon::parse($request->input('desde', $desdeDefecto->toDateString()))->startOfDay();
        $hasta = Carbon::parse($request->input('hasta', $hastaDefecto->toDateString()))->startOfDay();

        if ($desde->diffInDays($hasta) > 366) {
            throw ValidationException::withMessages(['hasta' => 'El rango no puede ser mayor a un año.']);
        }

        return [$desde, $hasta];
    }

    /**
     * Descarga un CSV que Excel abre correctamente (UTF-8 con BOM y separador ";").
     */
    private function csv(string $nombre, array $encabezados, iterable $filas)
    {
        return response()->streamDownload(function () use ($encabezados, $filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");               // BOM: tildes y ñ correctas en Excel
            fputcsv($salida, $encabezados, ';');
            foreach ($filas as $fila) {
                fputcsv($salida, $fila, ';');
            }
            fclose($salida);
        }, $nombre . '_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}