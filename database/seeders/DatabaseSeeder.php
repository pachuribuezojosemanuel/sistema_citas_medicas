<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermisoSeeder::class,
            RolSeeder::class,
            UserSeeder::class,           // Modulo 2
            CatalogoSeeder::class,       // Módulo 3
            EspecialidadSeeder::class,   // Módulo 4
            ConsultorioSeeder::class,    // Módulo 5
            MedicoSeeder::class,         // Módulos 6 y 7 (datos de prueba)
        ]);
    }
}
