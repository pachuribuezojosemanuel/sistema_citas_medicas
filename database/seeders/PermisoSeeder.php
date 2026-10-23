<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        // [código, nombre, módulo]
        $permisos = [
            ['USUARIO_GESTIONAR',      'Gestionar usuarios',                           'Usuarios'],
            ['ROL_GESTIONAR',          'Gestionar roles y permisos',                   'Usuarios'],
            ['BITACORA_VER',           'Consultar la bitácora',                        'Seguridad'],
            ['ESPECIALIDAD_GESTIONAR', 'Gestionar especialidades',                     'Especialidades'],
            ['CONSULTORIO_GESTIONAR',  'Gestionar consultorios',                       'Consultorios'],
            ['MEDICO_VER',             'Consultar médicos',                            'Médicos'],
            ['MEDICO_GESTIONAR',       'Registrar, modificar y desactivar médicos',    'Médicos'],
            ['HORARIO_VER',            'Consultar horarios de atención',               'Horarios'],
            ['HORARIO_GESTIONAR',      'Gestionar horarios de atención',               'Horarios'],
            ['PACIENTE_VER',           'Buscar y consultar pacientes',                 'Pacientes'],
            ['PACIENTE_GESTIONAR',     'Registrar, modificar y desactivar pacientes',  'Pacientes'],
            ['CITA_VER',               'Consultar citas',                              'Citas'],
            ['CITA_REGISTRAR',         'Programar citas',                              'Citas'],
            ['CITA_REPROGRAMAR',       'Reprogramar citas',                            'Citas'],
            ['CITA_CANCELAR',          'Cancelar citas',                               'Citas'],
            ['CITA_ASISTENCIA',        'Registrar asistencia e inasistencia',          'Citas'],
            ['CITA_ATENDER',           'Marcar una cita como atendida',                'Citas'],
            ['AGENDA_VER',             'Consultar la agenda de cualquier médico',      'Agenda'],
            ['MI_AGENDA',              'Consultar la agenda propia',                   'Agenda'],
            ['REPORTE_VER',            'Consultar reportes',                           'Reportes'],
            ['MIS_CITAS',              'Consultar y reservar citas propias',           'Portal del paciente'],
        ];

        foreach ($permisos as [$codigo, $nombre, $modulo]) {
            Permiso::create([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'modulo' => $modulo,
            ]);
        }
    }
}