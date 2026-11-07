<?php

namespace App\Http\Controllers;

use App\Exceptions\CitaException;
use App\Http\Requests\CitaRequest;
use App\Http\Requests\PerfilPacienteRequest;
use App\Models\Bitacora;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Paciente;
use App\Models\User;
use App\Services\CitaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Portal del paciente (sección 3.4): el paciente solo ve y modifica lo suyo.
 */
class PortalPacienteController extends Controller
{
    /** Máximo de citas pendientes que un paciente puede reservar por su cuenta */
    private const MAX_PENDIENTES = 3;

    /**
     * Mis citas: próximas e historial.
     */
    public function misCitas()
    {
        $paciente = $this->paciente();

        $citas = Cita::with(['medico', 'especialidad', 'estado', 'cancelacion'])
            ->where('paciente_id', $paciente->id)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        // Próximas: pendientes que todavía no empezaron
        [$proximas, $historial] = $citas->partition(fn ($c) => $c->esModificable() && $c->inicio()->isFuture());

        return view('portal.mis-citas', [
            'paciente'  => $paciente,
            'proximas'  => $proximas->sortBy(fn ($c) => $c->inicio())->values(),
            'historial' => $historial->values(),
        ]);
    }

    /**
     * Formulario de reserva.
     */
    public function reservar()
    {
        $paciente = $this->paciente();

        return view('portal.reservar', [
            'paciente'       => $paciente,
            'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(),
            'modalidades'    => Cita::MODALIDADES,
            'pendientes'     => $this->pendientes($paciente),
            'maximo'         => self::MAX_PENDIENTES,
        ]);
    }

    /**
     * Guarda la reserva con las mismas reglas que recepción (CitaService).
     */
    public function guardarReserva(CitaRequest $request, CitaService $servicio)
    {
        $paciente = $this->paciente();

        if ($this->pendientes($paciente) >= self::MAX_PENDIENTES) {
            return back()->withInput()->with('error',
                'Ya tiene ' . self::MAX_PENDIENTES . ' citas pendientes. Cancele una o comuníquese con recepción.');
        }

        $datos = $request->validated();
        $datos['paciente_id'] = $paciente->id;   // siempre para sí mismo, aunque se altere el formulario

        try {
            $cita = $servicio->registrar($datos, $this->usuario());
        } catch (CitaException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo registrar la reserva. Intente nuevamente.');
        }

        Bitacora::registrar('RESERVAR', 'Portal del paciente',
            "Cita {$cita->codigo} reservada por el paciente para el {$cita->fecha->format('d/m/Y')} {$cita->horario}");

        return redirect()->route('mis-citas.index')
            ->with('exito', "Cita {$cita->codigo} reservada para el {$cita->fecha->format('d/m/Y')} de {$cita->horario}.");
    }

    /**
     * El paciente cancela una de sus citas pendientes que aún no empezó.
     */
    public function cancelar(Request $request, Cita $cita, CitaService $servicio)
    {
        $paciente = $this->paciente();
        abort_unless($cita->paciente_id === $paciente->id, 403);

        $datos = $request->validate(
            ['motivo' => ['required', 'string', 'min:5', 'max:200']],
            ['motivo.required' => 'Indique el motivo de la cancelación.', 'motivo.min' => 'Describa el motivo con al menos 5 caracteres.']
        );

        if (! $cita->inicio()->isFuture()) {
            return back()->with('error', 'Solo puede cancelar citas que todavía no empezaron.');
        }

        try {
            $servicio->cancelar($cita, trim($datos['motivo']), $this->usuario());
        } catch (CitaException $e) {
            return back()->with('error', $e->getMessage());
        }

        Bitacora::registrar('CANCELAR', 'Portal del paciente', "Cita {$cita->codigo} cancelada por el paciente: {$datos['motivo']}");

        return back()->with('exito', "Cita {$cita->codigo} cancelada.");
    }

    /**
     * Mi perfil: datos personales, información médica y contactos.
     */
    public function perfil()
    {
        $paciente = $this->paciente();
        $paciente->load(['sexo', 'tipoSangre', 'alergias', 'enfermedades', 'contactosEmergencia']);

        return view('portal.perfil', [
            'paciente'  => $paciente,
            'contactos' => old('contactos', $paciente->contactosEmergencia
                ->map(fn ($c) => $c->only(['nombre_completo', 'parentesco', 'telefono']))
                ->all()),
        ]);
    }

    public function actualizarPerfil(PerfilPacienteRequest $request)
    {
        $paciente = $this->paciente();
        $datos    = $request->validated();

        DB::transaction(function () use ($paciente, $datos) {
            $paciente->update([
                'telefono'  => $datos['telefono'],
                'direccion' => $datos['direccion'] ?? null,
            ]);

            $paciente->contactosEmergencia()->delete();
            $paciente->contactosEmergencia()->createMany($datos['contactos'] ?? []);
        });

        Bitacora::registrar('MODIFICAR', 'Portal del paciente', "El paciente {$paciente->codigo} actualizó sus datos de contacto");

        return redirect()->route('mi-perfil.edit')->with('exito', 'Sus datos se actualizaron correctamente.');
    }

    // ------------------------------------------------------------------

    private function usuario(): User
    {
        /** @var User $usuario */
        $usuario = Auth::user();

        return $usuario;
    }

    /**
     * Paciente vinculado al usuario que inició sesión.
     */
    private function paciente(): Paciente
    {
        $paciente = $this->usuario()->paciente;
        abort_unless($paciente, 403);

        return $paciente;
    }

    private function pendientes(Paciente $paciente): int
    {
        return Cita::where('paciente_id', $paciente->id)
            ->pendientes()
            ->whereDate('fecha', '>=', today())
            ->count();
    }
}