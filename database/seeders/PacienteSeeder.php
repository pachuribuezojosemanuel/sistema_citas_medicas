<?php

namespace Database\Seeders;

use App\Models\Alergia;
use App\Models\Enfermedad;
use App\Models\Paciente;
use App\Models\Rol;
use App\Models\Sexo;
use App\Models\TipoSangre;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = [
            [
                'ci' => '7845123', 'nombres' => 'María', 'apellidos' => 'López Sánchez',
                'fecha_nacimiento' => '1990-05-14', 'sexo' => 'Femenino', 'sangre' => 'O+',
                'telefono' => '71234567', 'correo' => 'maria.lopez@gmail.com', 'direccion' => 'Av. Banzer #123',
                'cuenta' => 'maria.lopez',
                'alergias' => ['Penicilina' => 'Reacción severa'],
                'enfermedades' => [],
                'contactos' => [['nombre_completo' => 'Jorge López', 'parentesco' => 'Esposo', 'telefono' => '72345678']],
            ],
            [
                'ci' => '5632147', 'nombres' => 'Pedro', 'apellidos' => 'Quispe Mamani',
                'fecha_nacimiento' => '1958-11-02', 'sexo' => 'Masculino', 'sangre' => 'A+',
                'telefono' => '76543210', 'correo' => null, 'direccion' => 'Calle Sucre #45',
                'cuenta' => null,
                'alergias' => [],
                'enfermedades' => ['Diabetes tipo 2' => 'Controlada con metformina', 'Hipertensión arterial' => null],
                'contactos' => [['nombre_completo' => 'Rosa Quispe', 'parentesco' => 'Hija', 'telefono' => '70011223']],
            ],
            [
                'ci' => '14523698', 'nombres' => 'Lucía', 'apellidos' => 'Fernández Rojas',
                'fecha_nacimiento' => '2018-03-20', 'sexo' => 'Femenino', 'sangre' => null,
                'telefono' => '78889990', 'correo' => null, 'direccion' => null,
                'cuenta' => null,
                'alergias' => ['Maní' => null],
                'enfermedades' => ['Asma' => null],
                'contactos' => [['nombre_completo' => 'Carla Rojas', 'parentesco' => 'Madre', 'telefono' => '78889990']],
            ],
        ];

        $rolPaciente = Rol::where('nombre', 'Paciente')->firstOrFail();
        $hoy = now()->toDateString();

        foreach ($pacientes as $d) {
            if (Paciente::where('ci', $d['ci'])->exists()) {
                continue;   // no duplicar
            }

            DB::transaction(function () use ($d, $rolPaciente, $hoy) {
                $userId = null;
                if ($d['cuenta']) {
                    $userId = User::create([
                        'rol_id'   => $rolPaciente->id,
                        'name'     => $d['cuenta'],
                        'email'    => $d['correo'],
                        'password' => 'Paciente123*',
                    ])->id;
                }

                $paciente = Paciente::create([
                    'user_id'          => $userId,
                    'codigo'           => Paciente::siguienteCodigo(),
                    'ci'               => $d['ci'],
                    'nombres'          => $d['nombres'],
                    'apellidos'        => $d['apellidos'],
                    'fecha_nacimiento' => $d['fecha_nacimiento'],
                    'sexo_id'          => Sexo::where('nombre', $d['sexo'])->value('id'),
                    'tipo_sangre_id'   => $d['sangre'] ? TipoSangre::where('nombre', $d['sangre'])->value('id') : null,
                    'telefono'         => $d['telefono'],
                    'correo'           => $d['correo'],
                    'direccion'        => $d['direccion'],
                ]);

                foreach ($d['alergias'] as $nombre => $obs) {
                    $paciente->alergias()->attach(Alergia::where('nombre', $nombre)->value('id'),
                        ['observacion' => $obs, 'fecha_registro' => $hoy]);
                }

                foreach ($d['enfermedades'] as $nombre => $obs) {
                    $paciente->enfermedades()->attach(Enfermedad::where('nombre', $nombre)->value('id'),
                        ['observacion' => $obs]);
                }

                $paciente->contactosEmergencia()->createMany($d['contactos']);
            });
        }
    }
}