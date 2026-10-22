<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 12)->unique();        // Ej.: CM-0001
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('medico_id')->constrained('medicos');
            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->foreignId('estado_cita_id')->constrained('estado_citas');
            $table->foreignId('user_id')->constrained('users');    // registrada por
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('motivo', 200)->nullable();
            $table->string('modalidad', 20)->default('Presencial'); // Presencial o Virtual
            $table->timestamps();                          // created_at = fecha_creacion

            $table->index(['medico_id', 'fecha']);         // acelera la verificación de disponibilidad
        });

        DB::statement('ALTER TABLE citas ADD CONSTRAINT chk_cita_horas CHECK (hora_fin > hora_inicio)');
        DB::statement("ALTER TABLE citas ADD CONSTRAINT chk_cita_modalidad CHECK (modalidad IN ('Presencial', 'Virtual'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
