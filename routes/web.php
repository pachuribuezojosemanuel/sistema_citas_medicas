<?php

use App\Http\Controllers\BitacoraController;
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
});

require __DIR__ . '/auth.php';