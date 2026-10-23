<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Consulta de la bitácora (solo lectura).
 */
class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'user_id' => ['nullable', 'integer'],
            'accion'  => ['nullable', 'string', 'max:50'],
            'desde'   => ['nullable', 'date'],
            'hasta'   => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $registros = Bitacora::with('user')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('accion'),  fn ($q) => $q->where('accion', $request->input('accion')))
            ->when($request->filled('desde'),   fn ($q) => $q->whereDate('fecha_hora', '>=', $request->input('desde')))
            ->when($request->filled('hasta'),   fn ($q) => $q->whereDate('fecha_hora', '<=', $request->input('hasta')))
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $usuarios = User::orderBy('name')->get(['id', 'name']);
        $acciones = Bitacora::select('accion')->distinct()->orderBy('accion')->pluck('accion');

        return view('bitacora.index', compact('registros', 'usuarios', 'acciones'));
    }
}