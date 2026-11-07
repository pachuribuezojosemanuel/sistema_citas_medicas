<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Página de inicio: muestra un resumen distinto según el rol.
 */
class DashboardController extends Controller
{
    public function __invoke()
    {
        /** @var User $usuario */
        $usuario = Auth::user();
        $usuario->load(['rol', 'personal', 'medico', 'paciente']);

        $datos = ['usuario' => $usuario];

        // Paciente: su próxima cita
        if ($usuario->paciente) {
            $datos['proximaCita'] = Cita::with(['medico', 'especialidad', 'estado'])
                ->where('paciente_id', $usuario->paciente->id)
                ->pendientes()
                ->whereDate('fecha', '>=', today())
                ->orderBy('fecha')->orderBy('hora_inicio')
                ->first();
        }

        // Médico: sus citas de hoy (sin las canceladas)
        if ($usuario->medico) {
            $datos['citasHoy'] = Cita::with(['paciente', 'estado'])
                ->where('medico_id', $usuario->medico->id)
                ->whereDate('fecha', today())
                ->where('estado_cita_id', '!=', EstadoCita::idDe(EstadoCita::CANCELADA))
                ->orderBy('hora_inicio')
                ->get();
        }

        // Personal: citas de hoy por estado
        if ($usuario->tienePermiso('CITA_VER')) {
            $datos['resumenHoy'] = EstadoCita::withCount(['citas' => fn ($q) => $q->whereDate('fecha', today())])
                ->orderBy('id')
                ->get();
        }

        return view('dashboard', $datos);
    }
}