<?php

namespace App\Http\Controllers;

use App\Http\Requests\EspecialidadRequest;
use App\Models\Bitacora;
use App\Models\Especialidad;
use Illuminate\Http\Request;

/**
 * Gestión de especialidades médicas (RF04).
 * No se eliminan: se activan o desactivan.
 */
class EspecialidadController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $especialidades = Especialidad::withCount('medicos')
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'ilike', "%{$buscar}%"))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado') === '1'))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('especialidades.index', compact('especialidades', 'buscar'));
    }

    public function create()
    {
        return view('especialidades.create', ['especialidad' => new Especialidad()]);
    }

    public function store(EspecialidadRequest $request)
    {
        $especialidad = Especialidad::create($request->validated());

        Bitacora::registrar('REGISTRAR', 'Especialidades', "Especialidad creada: {$especialidad->nombre}");

        return redirect()->route('especialidades.index')
            ->with('exito', "Especialidad {$especialidad->nombre} registrada correctamente.");
    }

    public function edit(Especialidad $especialidad)
    {
        return view('especialidades.edit', compact('especialidad'));
    }

    public function update(EspecialidadRequest $request, Especialidad $especialidad)
    {
        $anterior = $especialidad->nombre;
        $especialidad->update($request->validated());

        Bitacora::registrar('MODIFICAR', 'Especialidades', "Especialidad modificada: {$anterior} → {$especialidad->nombre}");

        return redirect()->route('especialidades.index')
            ->with('exito', "Especialidad {$especialidad->nombre} actualizada correctamente.");
    }

    /**
     * Activa o desactiva. Una especialidad inactiva no se ofrece para nuevas citas,
     * pero sus citas y médicos históricos se conservan.
     */
    public function cambiarEstado(Especialidad $especialidad)
    {
        $especialidad->update(['estado' => ! $especialidad->estado]);

        $accion = $especialidad->estado ? 'ACTIVAR' : 'DESACTIVAR';
        Bitacora::registrar($accion, 'Especialidades', "Especialidad {$especialidad->nombre}");

        return back()->with('exito', $especialidad->estado
            ? "Especialidad {$especialidad->nombre} activada."
            : "Especialidad {$especialidad->nombre} desactivada. No estará disponible para nuevas citas.");
    }
}