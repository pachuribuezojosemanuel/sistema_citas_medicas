<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            'Medicina general' => 'Atención primaria, diagnóstico y tratamiento de enfermedades comunes',
            'Pediatría'        => 'Atención médica de niños y adolescentes',
            'Ginecología'      => 'Salud del sistema reproductor femenino',
            'Odontología'      => 'Diagnóstico y tratamiento de la salud bucal',
            'Cardiología'      => 'Diagnóstico y tratamiento de enfermedades del corazón',
        ];

        foreach ($especialidades as $nombre => $descripcion) {
            Especialidad::firstOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
        }
    }
}