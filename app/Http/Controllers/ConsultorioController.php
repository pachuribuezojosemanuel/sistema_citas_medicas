<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultorioRequest;
use App\Models\Bitacora;
use App\Models\Consultorio;
use Illuminate\Http\Request;

/**
 * Gestión de consultorios. No se eliminan: se activan o desactivan.
 */
class ConsultorioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $consultorios = Consultorio::withCount('horarios')
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(function ($q) use ($buscar) {
                    $q->where('numero', 'ilike', "%{$buscar}%")
                      ->orWhere('nombre', 'ilike', "%{$buscar}%");
                });
            })
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->orderBy('numero')
            ->paginate(10)
            ->withQueryString();

        return view('consultorios.index', compact('consultorios', 'buscar'));
    }

    public function create()
    {
        return view('consultorios.create', ['consultorio' => new Consultorio()]);
    }

    public function store(ConsultorioRequest $request)
    {
        $consultorio = Consultorio::create($request->validated());

        Bitacora::registrar('REGISTRAR', 'Consultorios', "Consultorio creado: {$consultorio->numero} - {$consultorio->nombre}");

        return redirect()->route('consultorios.index')
            ->with('exito', "Consultorio {$consultorio->numero} registrado correctamente.");
    }

    public function edit(Consultorio $consultorio)
    {
        return view('consultorios.edit', compact('consultorio'));
    }

    public function update(ConsultorioRequest $request, Consultorio $consultorio)
    {
        $consultorio->update($request->validated());

        Bitacora::registrar('MODIFICAR', 'Consultorios', "Consultorio modificado: {$consultorio->numero}");

        return redirect()->route('consultorios.index')
            ->with('exito', "Consultorio {$consultorio->numero} actualizado correctamente.");
    }

    /**
     * Un consultorio inactivo no se ofrece al crear nuevos horarios.
     */
    public function cambiarEstado(Consultorio $consultorio)
    {
        $consultorio->update(['estado' => ! $consultorio->estado]);

        $accion = $consultorio->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Consultorios', "Consultorio {$consultorio->numero}");

        return back()->with('exito', $consultorio->estado
            ? "Consultorio {$consultorio->numero} activado."
            : "Consultorio {$consultorio->numero} desactivado.");
    }
}