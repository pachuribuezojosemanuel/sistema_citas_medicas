<?php

namespace Database\Seeders;

use App\Models\Consultorio;
use App\Models\Especialidad;
use App\Models\HorarioAtencion;
use App\Models\Medico;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicoSeeder extends Seeder
{
    public function run(): void
    {
        $rolMedico = Rol::where('nombre', 'Médico')->firstOrFail();

        $medicos = [
            [
                'usuario' => 'dr.perez', 'registro' => 'MP-10001',
                'nombres' => 'Juan', 'apellidos' => 'Pérez Gutiérrez',
                'telefono' => '70010001', 'correo' => 'juan.perez@vidasalud.com',
                'especialidades' => ['Medicina general'],
                // [día (1=Lunes), inicio, fin, consultorio]
                'horarios' => [[1, '08:00', '12:00', 'C-101'], [3, '14:00', '18:00', 'C-101']],
            ],
            [
                'usuario' => 'dra.rojas', 'registro' => 'MP-10002',
                'nombres' => 'Ana María', 'apellidos' => 'Rojas Vargas',
                'telefono' => '70010002', 'correo' => 'ana.rojas@vidasalud.com',
                'especialidades' => ['Pediatría'],
                'horarios' => [[2, '09:00', '13:00', 'C-102'], [4, '09:00', '13:00', 'C-102']],
            ],
            [
                'usuario' => 'dr.mendoza', 'registro' => 'MP-10003',
                'nombres' => 'Carlos', 'apellidos' => 'Mendoza Flores',
                'telefono' => '70010003', 'correo' => 'carlos.mendoza@vidasalud.com',
                'especialidades' => ['Cardiología', 'Medicina general'],
                'horarios' => [[5, '08:00', '12:00', 'C-203'], [6, '08:00', '11:00', 'C-203']],
            ],
        ];

        foreach ($medicos as $d) {
            // No duplicar si ya existe
            if (Medico::where('registro_profesional', $d['registro'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($d, $rolMedico) {
                $user = User::create([
                    'rol_id'   => $rolMedico->id,
                    'name'     => $d['usuario'],
                    'email'    => $d['correo'],
                    'password' => 'Medico123*',
                ]);

                $medico = Medico::create([
                    'user_id'              => $user->id,
                    'codigo'               => Medico::siguienteCodigo(),
                    'registro_profesional' => $d['registro'],
                    'nombres'              => $d['nombres'],
                    'apellidos'            => $d['apellidos'],
                    'telefono'             => $d['telefono'],
                    'correo'               => $d['correo'],
                ]);

                $ids = Especialidad::whereIn('nombre', $d['especialidades'])->pluck('id');
                $medico->especialidades()->attach(
                    $ids->mapWithKeys(fn ($id) => [$id => ['fecha_asignacion' => now()->toDateString()]])->all()
                );

                foreach ($d['horarios'] as [$dia, $inicio, $fin, $numero]) {
                    HorarioAtencion::create([
                        'medico_id'      => $medico->id,
                        'dia_semana_id'  => $dia,
                        'consultorio_id' => Consultorio::where('numero', $numero)->value('id'),
                        'hora_inicio'    => $inicio,
                        'hora_fin'       => $fin,
                        'duracion_cita'  => 30,
                    ]);
                }
            });
        }
    }
}