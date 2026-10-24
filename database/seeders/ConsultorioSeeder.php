<?php

namespace Database\Seeders;

use App\Models\Consultorio;
use Illuminate\Database\Seeder;

class ConsultorioSeeder extends Seeder
{
    public function run(): void
    {
        $consultorios = [
            ['numero' => 'C-101', 'nombre' => 'Consultorio Medicina General', 'piso' => '1'],
            ['numero' => 'C-102', 'nombre' => 'Consultorio Pediatría',        'piso' => '1'],
            ['numero' => 'C-201', 'nombre' => 'Consultorio Ginecología',      'piso' => '2'],
            ['numero' => 'C-202', 'nombre' => 'Consultorio Odontología',      'piso' => '2'],
            ['numero' => 'C-203', 'nombre' => 'Consultorio Cardiología',      'piso' => '2'],
        ];

        foreach ($consultorios as $c) {
            Consultorio::firstOrCreate(['numero' => $c['numero']], $c);
        }
    }
}