<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_enfermedad', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('enfermedad_id')->constrained('enfermedades')->cascadeOnDelete();
            $table->date('fecha_diagnostico')->nullable();
            $table->string('observacion', 200)->nullable(); // Ej.: controlada con tratamiento

            $table->primary(['paciente_id', 'enfermedad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_enfermedad');
    }
};
