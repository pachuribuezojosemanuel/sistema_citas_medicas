<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        // 3.1 Administrador: configura y supervisa el sistema
        $admin = Rol::create([
            'nombre'      => 'Administrador',
            'descripcion' => 'Configura y supervisa el sistema',
        ]);
        $admin->permisos()->attach(
            Permiso::whereNotIn('codigo', ['MI_AGENDA', 'MIS_CITAS'])->pluck('id')
        );

        // 3.2 Recepcionista: gestiona las citas de los pacientes
        $recepcionista = Rol::create([
            'nombre'      => 'Recepcionista',
            'descripcion' => 'Gestiona pacientes y citas',
        ]);
        $recepcionista->permisos()->attach(
            Permiso::whereIn('codigo', [
                'PACIENTE_VER', 'PACIENTE_GESTIONAR',
                'MEDICO_VER', 'HORARIO_VER',
                'CITA_VER', 'CITA_REGISTRAR', 'CITA_REPROGRAMAR', 'CITA_CANCELAR', 'CITA_ASISTENCIA',
                'AGENDA_VER',
            ])->pluck('id')
        );

        // 3.3 Médico: consulta su agenda
        $medico = Rol::create([
            'nombre'      => 'Médico',
            'descripcion' => 'Consulta su agenda y atiende citas',
        ]);
        $medico->permisos()->attach(
            Permiso::whereIn('codigo', ['MI_AGENDA', 'CITA_ATENDER'])->pluck('id')
        );

        // 3.4 Paciente: portal del paciente
        $paciente = Rol::create([
            'nombre'      => 'Paciente',
            'descripcion' => 'Consulta y reserva sus propias citas',
        ]);
        $paciente->permisos()->attach(
            Permiso::whereIn('codigo', ['MIS_CITAS'])->pluck('id')
        );
    }
}