<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->foreignId('sexo_id')->constrained('sexos');
            $table->foreignId('tipo_sangre_id')->nullable()->constrained('tipo_sangres'); // 0..1
            $table->string('codigo', 10)->unique();        // Ej.: PAC-0001
            $table->string('ci', 15)->unique();
            $table->string('nombres', 60);
            $table->string('apellidos', 60);
            $table->date('fecha_nacimiento');
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->text('medicamentos')->nullable();
            $table->text('antecedentes')->nullable();
            $table->boolean('estado')->default(true);      // Se desactiva, nunca se elimina
            $table->timestamps();                          // created_at = fecha_registro
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
