<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacto_emergencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete(); // composición
            $table->string('nombre_completo', 100);
            $table->string('parentesco', 30);              // Madre, esposo, hijo...
            $table->string('telefono', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_emergencias');
    }
};
