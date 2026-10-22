<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_alergia', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('alergia_id')->constrained('alergias')->cascadeOnDelete();
            $table->string('observacion', 200)->nullable(); // Ej.: reacción severa
            $table->date('fecha_registro')->nullable();

            $table->primary(['paciente_id', 'alergia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_alergia');
    }
};
