<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // La contraseña se cifra automáticamente (cast 'hashed' → password_hash)
        User::create([
            'rol_id'   => Rol::where('nombre', 'Administrador')->value('id'),
            'name'     => 'admin',
            'email'    => 'admin@vidasalud.com',
            'password' => 'Admin123*',
        ]);

        User::create([
            'rol_id'   => Rol::where('nombre', 'Recepcionista')->value('id'),
            'name'     => 'recepcion',
            'email'    => 'recepcion@vidasalud.com',
            'password' => 'Recep123*',
        ]);

        // Los usuarios de médicos y pacientes se crean en sus propios módulos
    }
}