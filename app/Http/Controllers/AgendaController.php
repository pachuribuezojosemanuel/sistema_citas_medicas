<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Medico;
use App\Models\User;
use App\Services\DisponibilidadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Agenda médica por fecha (RF11).
 */
class AgendaController extends Controller
{
    /**
     * Agenda de cualquier médico (Administrador, Recepcionista).
     */
    public function index(Request $request, DisponibilidadService $disponibilidad)
    {
        [$fecha, $estadoId] = $this->filtros($request);

        $medico = $request->filled('medico_id') ? Medico::find($request->integer('medico_id')) : null;

        return view('agenda.index', $this->datos($medico, $fecha, $estadoId, $disponibilidad) + [
            'propia'  => false,
            'medicos' => Medico::orderBy('apellidos')->orderBy('nombres')->get(),
        ]);
    }

    /**
     * Agenda del médico que inició sesión.
     */
    public function mia(Request $request, DisponibilidadService $disponibilidad)
    {
        /** @var User $usuario */
        $usuario = Auth::user();
        abort_unless($usuario->medico, 403);

        [$fecha, $estadoId] = $this->filtros($request);

        return view('agenda.index', $this->datos($usuario->medico, $fecha, $estadoId, $disponibilidad) + [
            'propia'  => true,
            'medicos' => collect(),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: int|null}
     */
    private function filtros(Request $request): array
    {
        $request->validate([
            'fecha'          => ['nullable', 'date_format:Y-m-d'],
            'medico_id'      => ['nullable', 'integer'],
            'estado_cita_id' => ['nullable', 'integer'],
        ]);

        $fecha = Carbon::parse($request->input('fecha', today()->toDateString()))->startOfDay();

        return [$fecha, $request->integer('estado_cita_id') ?: null];
    }

    private function datos(?Medico $medico, Carbon $fecha, ?int $estadoId, DisponibilidadService $disponibilidad): array
    {
        $citas   = collect();
        $libres  = [];
        $resumen = collect();

        if ($medico) {
            $delDia = Cita::with(['paciente', 'especialidad', 'estado'])
                ->where('medico_id', $medico->id)
                ->whereDate('fecha', $fecha)
                ->orderBy('hora_inicio')
                ->get();

            $resumen = $delDia->countBy(fn ($c) => $c->estado->nombre);
            $citas   = $estadoId ? $delDia->where('estado_cita_id', $estadoId)->values() : $delDia;
            $libres  = $disponibilidad->bloquesLibres($medico, $fecha);
        }

        return [
            'medico'   => $medico,
            'fecha'    => $fecha,
            'citas'    => $citas,
            'libres'   => $libres,
            'resumen'  => $resumen,
            'estados'  => EstadoCita::orderBy('id')->get(),
            'estadoId' => $estadoId,
        ];
    }
}