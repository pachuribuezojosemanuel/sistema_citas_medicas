<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_atencions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos');
            $table->foreignId('dia_semana_id')->constrained('dia_semanas');
            $table->foreignId('consultorio_id')->constrained('consultorios');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->smallInteger('duracion_cita')->default(30);   // minutos
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // hora_fin debe ser mayor que hora_inicio
        DB::statement('ALTER TABLE horario_atencions ADD CONSTRAINT chk_horario_horas CHECK (hora_fin > hora_inicio)');
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_atencions');
    }
};
