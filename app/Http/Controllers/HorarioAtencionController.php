<?php

namespace App\Http\Controllers;

use App\Http\Requests\HorarioRequest;
use App\Models\Bitacora;
use App\Models\Consultorio;
use App\Models\DiaSemana;
use App\Models\HorarioAtencion;
use App\Models\Medico;
use Illuminate\Http\Request;

/**
 * Gestión de horarios de atención (RF05).
 */
class HorarioAtencionController extends Controller
{
    public function index(Request $request)
    {
        $horarios = HorarioAtencion::with(['medico', 'diaSemana', 'consultorio'])
            ->when($request->filled('medico_id'), fn ($q) => $q->where('medico_id', $request->integer('medico_id')))
            ->when($request->filled('dia_semana_id'), fn ($q) => $q->where('dia_semana_id', $request->integer('dia_semana_id')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->orderBy('dia_semana_id')
            ->orderBy('hora_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('horarios.index', [
            'horarios' => $horarios,
            'medicos'  => Medico::orderBy('apellidos')->orderBy('nombres')->get(),
            'dias'     => DiaSemana::orderBy('id')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $horario = new HorarioAtencion([
            'medico_id'     => $request->integer('medico_id') ?: null,   // viene desde la ficha del médico
            'duracion_cita' => 30,
        ]);

        return view('horarios.create', $this->datosFormulario($horario));
    }

    public function store(HorarioRequest $request)
    {
        $horario = HorarioAtencion::create($request->validated());
        $horario->load(['medico', 'diaSemana']);

        Bitacora::registrar('REGISTRAR', 'Horarios',
            "Horario {$horario->diaSemana->nombre} {$horario->rango} - {$horario->medico->nombre_completo}");

                $citas = $horario->cantidad_bloques . ' ' . ($horario->cantidad_bloques === 1 ? 'cita' : 'citas');

        return redirect()->route('horarios.index')
            ->with('exito', "Horario registrado: {$horario->diaSemana->nombre} {$horario->rango} ({$citas} de {$horario->duracion_cita} min).");
    }

    public function edit(HorarioAtencion $horario)
    {
        return view('horarios.edit', $this->datosFormulario($horario));
    }

    public function update(HorarioRequest $request, HorarioAtencion $horario)
    {
        $horario->update($request->validated());
        $horario->load(['medico', 'diaSemana']);

        Bitacora::registrar('MODIFICAR', 'Horarios',
            "Horario {$horario->diaSemana->nombre} {$horario->rango} - {$horario->medico->nombre_completo}");

        return redirect()->route('horarios.index')->with('exito', 'Horario actualizado correctamente.');
    }

    /**
     * Activa o desactiva. Al activar se vuelve a comprobar que no choque con otro horario.
     */
    public function cambiarEstado(HorarioAtencion $horario)
    {
        $horario->load(['medico', 'diaSemana', 'consultorio']);

        if (! $horario->estado) {
            foreach (['medico_id', 'consultorio_id'] as $campo) {
                $choque = HorarioAtencion::buscarChoque($campo, $horario->$campo, $horario->dia_semana_id,
                    $horario->hora_inicio, $horario->hora_fin, $horario->id);

                if ($choque) {
                    return back()->with('error',
                        "No se puede activar: se superpone con el horario {$choque->rango} de {$choque->medico->nombre_completo} (consultorio {$choque->consultorio->numero}).");
                }
            }
        }

        $horario->update(['estado' => ! $horario->estado]);

        $accion = $horario->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Horarios',
            "Horario {$horario->diaSemana->nombre} {$horario->rango} - {$horario->medico->nombre_completo}");

        return back()->with('exito', $horario->estado ? 'Horario activado.' : 'Horario desactivado. Ya no se ofrecerán citas en ese horario.');
    }

    /**
     * Listas para los selectores del formulario.
     */
    private function datosFormulario(HorarioAtencion $horario): array
    {
        return [
            'horario'      => $horario,
            'medicos'      => Medico::where('estado', true)->orWhere('id', $horario->medico_id)
                                ->orderBy('apellidos')->orderBy('nombres')->get(),
            'dias'         => DiaSemana::orderBy('id')->get(),
            'consultorios' => Consultorio::where('estado', true)->orWhere('id', $horario->consultorio_id)
                                ->orderBy('numero')->get(),
            'duraciones'   => [15, 20, 30, 45, 60],
        ];
    }
}