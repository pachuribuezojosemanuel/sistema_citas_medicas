<?php

/*
| Menú principal del sistema.
| - ruta:    nombre de la ruta (route name)
| - permiso: código de permiso requerido (null = cualquier usuario con sesión)
| - items:   submenú desplegable
*/

return [
    ['texto' => 'Inicio', 'ruta' => 'dashboard', 'permiso' => null],

    ['texto' => 'Citas', 'items' => [
        ['texto' => 'Citas',     'ruta' => 'citas.index',     'permiso' => 'CITA_VER'],
        ['texto' => 'Agenda',    'ruta' => 'agenda.index',    'permiso' => 'AGENDA_VER'],
        ['texto' => 'Mi agenda', 'ruta' => 'mi-agenda.index', 'permiso' => 'MI_AGENDA'],
        ['texto' => 'Mis citas', 'ruta' => 'mis-citas.index', 'permiso' => 'MIS_CITAS'],
    ]],

    ['texto' => 'Pacientes', 'ruta' => 'pacientes.index', 'permiso' => 'PACIENTE_VER'],

    ['texto' => 'Médicos', 'items' => [
        ['texto' => 'Médicos',        'ruta' => 'medicos.index',        'permiso' => 'MEDICO_VER'],
        ['texto' => 'Horarios',       'ruta' => 'horarios.index',       'permiso' => 'HORARIO_VER'],
        ['texto' => 'Especialidades', 'ruta' => 'especialidades.index', 'permiso' => 'ESPECIALIDAD_GESTIONAR'],
        ['texto' => 'Consultorios',   'ruta' => 'consultorios.index',   'permiso' => 'CONSULTORIO_GESTIONAR'],
    ]],

    ['texto' => 'Reportes', 'ruta' => 'reportes.index', 'permiso' => 'REPORTE_VER'],

    ['texto' => 'Administración', 'items' => [
        ['texto' => 'Usuarios',          'ruta' => 'usuarios.index', 'permiso' => 'USUARIO_GESTIONAR'],
        ['texto' => 'Roles y permisos',  'ruta' => 'roles.index',    'permiso' => 'ROL_GESTIONAR'],
        ['texto' => 'Bitácora',          'ruta' => 'bitacora.index', 'permiso' => 'BITACORA_VER'],
        ['texto' => 'Usuarios y personal', 'ruta' => 'usuarios.index', 'permiso' => 'USUARIO_GESTIONAR'],
    ]],
];
