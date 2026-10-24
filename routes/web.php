<?php

use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\ConsultorioController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\HorarioAtencionController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// La raíz lleva al inicio (si no hay sesión, 'auth' redirige al login)
Route::redirect('/', '/dashboard');

// Todas las páginas internas requieren sesión válida y usuario activo (RF01)
Route::middleware(['auth', 'activo'])->group(function () {

    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Perfil propio (sin eliminación de cuenta)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ===== Módulo 2: Usuarios =====
    Route::middleware('permiso:USUARIO_GESTIONAR')->group(function () {
        Route::resource('usuarios', UserController::class)
            ->except(['show', 'destroy'])
            ->parameters(['usuarios' => 'usuario']);

        Route::patch('usuarios/{usuario}/estado', [UserController::class, 'cambiarEstado'])
            ->name('usuarios.estado');
    });

    // ===== Módulo 2: Roles y permisos =====
    Route::middleware('permiso:ROL_GESTIONAR')->group(function () {
        Route::resource('roles', RolController::class)
            ->except(['show', 'destroy'])
            ->parameters(['roles' => 'rol']);

        Route::patch('roles/{rol}/estado', [RolController::class, 'cambiarEstado'])
            ->name('roles.estado');
    });

    // ===== Módulo 2: Bitácora =====
    Route::get('bitacora', [BitacoraController::class, 'index'])
        ->middleware('permiso:BITACORA_VER')
        ->name('bitacora.index');

    // ===== Módulo 4: Especialidades (RF04) =====
    Route::middleware('permiso:ESPECIALIDAD_GESTIONAR')->group(function () {
        Route::resource('especialidades', EspecialidadController::class)
            ->except(['show', 'destroy'])
            ->parameters(['especialidades' => 'especialidad']);

        Route::patch('especialidades/{especialidad}/estado', [EspecialidadController::class, 'cambiarEstado'])
            ->name('especialidades.estado');
    });

    // ===== Módulo 5: Consultorios =====
    Route::middleware('permiso:CONSULTORIO_GESTIONAR')->group(function () {
        Route::resource('consultorios', ConsultorioController::class)
            ->except(['show', 'destroy'])
            ->parameters(['consultorios' => 'consultorio']);

        Route::patch('consultorios/{consultorio}/estado', [ConsultorioController::class, 'cambiarEstado'])
            ->name('consultorios.estado');
    });

    // ===== Módulo 6: Médicos (RF03) =====
    // Primero las rutas de gestión, para que /medicos/create no se confunda con /medicos/{medico}
    Route::middleware('permiso:MEDICO_GESTIONAR')->group(function () {
        Route::resource('medicos', MedicoController::class)
            ->only(['create', 'store', 'edit', 'update'])
            ->parameters(['medicos' => 'medico']);

        Route::patch('medicos/{medico}/estado', [MedicoController::class, 'cambiarEstado'])
            ->name('medicos.estado');
    });

    // Consulta: listado y agenda (Administrador y Recepcionista)
    Route::middleware('permiso:MEDICO_VER')->group(function () {
        Route::resource('medicos', MedicoController::class)
            ->only(['index', 'show'])
            ->parameters(['medicos' => 'medico']);
    });

    // ===== Módulo 7: Horarios de atención (RF05) =====
    Route::middleware('permiso:HORARIO_GESTIONAR')->group(function () {
        Route::resource('horarios', HorarioAtencionController::class)
            ->only(['create', 'store', 'edit', 'update'])
            ->parameters(['horarios' => 'horario']);

        Route::patch('horarios/{horario}/estado', [HorarioAtencionController::class, 'cambiarEstado'])
            ->name('horarios.estado');
    });

    Route::middleware('permiso:HORARIO_VER')->group(function () {
        Route::get('horarios', [HorarioAtencionController::class, 'index'])->name('horarios.index');
    });

    // ===== Módulo 8: Pacientes (RF02) =====
    // Primero las de gestión, para que /pacientes/create no se confunda con /pacientes/{paciente}
    Route::middleware('permiso:PACIENTE_GESTIONAR')->group(function () {
        Route::resource('pacientes', PacienteController::class)
            ->only(['create', 'store', 'edit', 'update'])
            ->parameters(['pacientes' => 'paciente']);

        Route::patch('pacientes/{paciente}/estado', [PacienteController::class, 'cambiarEstado'])
            ->name('pacientes.estado');
    });

    Route::middleware('permiso:PACIENTE_VER')->group(function () {
        Route::resource('pacientes', PacienteController::class)
            ->only(['index', 'show'])
            ->parameters(['pacientes' => 'paciente']);
    });
});

require __DIR__ . '/auth.php';
