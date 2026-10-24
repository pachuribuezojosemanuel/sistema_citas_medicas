<?php

namespace Database\Seeders;

use App\Models\Alergia;
use App\Models\Enfermedad;
use App\Models\EstadoCita;
use App\Models\Sexo;
use App\Models\TipoSangre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Sexo ----
        foreach (['Masculino', 'Femenino'] as $nombre) {
            Sexo::firstOrCreate(['nombre' => $nombre]);
        }

        // ---- Tipo de sangre ----
        foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $nombre) {
            TipoSangre::firstOrCreate(['nombre' => $nombre]);
        }

        // ---- Días de la semana (id fijo 1..7, norma ISO) ----
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $filas = [];
        foreach ($dias as $i => $nombre) {
            $filas[] = ['id' => $i + 1, 'nombre' => $nombre, 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('dia_semanas')->upsert($filas, ['id'], ['nombre', 'updated_at']);
        // Ajusta el contador de PostgreSQL después de insertar ids manuales
        DB::statement("SELECT setval(pg_get_serial_sequence('dia_semanas', 'id'), (SELECT MAX(id) FROM dia_semanas))");

        // ---- Estados de cita (RF10) ----
        $estados = [
            EstadoCita::PROGRAMADA   => 'La cita está reservada',
            EstadoCita::CONFIRMADA   => 'Se ha confirmado la asistencia',
            EstadoCita::ATENDIDA     => 'La consulta se realizó',
            EstadoCita::CANCELADA    => 'La cita fue cancelada',
            EstadoCita::INASISTENCIA => 'El paciente no se presentó',
        ];
        foreach ($estados as $nombre => $descripcion) {
            EstadoCita::firstOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
        }

        // ---- Alergias frecuentes ----
        $alergias = [
            'Penicilina', 'Amoxicilina', 'Sulfamidas', 'Ácido acetilsalicílico (Aspirina)',
            'Ibuprofeno', 'Látex', 'Mariscos', 'Maní', 'Polen', 'Ácaros del polvo',
        ];
        foreach ($alergias as $nombre) {
            Alergia::firstOrCreate(['nombre' => $nombre]);
        }

        // ---- Enfermedades crónicas frecuentes ----
        $enfermedades = [
            'Diabetes tipo 1', 'Diabetes tipo 2', 'Hipertensión arterial', 'Asma',
            'Enfermedad pulmonar obstructiva crónica (EPOC)', 'Hipotiroidismo', 'Hipertiroidismo',
            'Insuficiencia renal crónica', 'Cardiopatía isquémica', 'Artritis reumatoide',
            'Epilepsia', 'Obesidad',
        ];
        foreach ($enfermedades as $nombre) {
            Enfermedad::firstOrCreate(['nombre' => $nombre]);
        }
    }
}