<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        $personal = [
            'admin' => [
                'ci' => '4567891', 'nombres' => 'Roberto', 'apellidos' => 'Salazar Vaca',
                'telefono' => '70000001', 'cargo' => 'Administrador del sistema', 'fecha_ingreso' => '2024-01-15',
            ],
            'recepcion' => [
                'ci' => '6789123', 'nombres' => 'Carmen', 'apellidos' => 'Justiniano Rivero',
                'telefono' => '70000002', 'cargo' => 'Recepcionista', 'fecha_ingreso' => '2025-03-01',
            ],
        ];

        foreach ($personal as $usuario => $datos) {
            $user = User::where('name', $usuario)->first();

            if ($user && ! $user->personal) {
                $user->personal()->create($datos);
            }
        }
    }
}