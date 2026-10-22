<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('codigo', 10)->unique();                 // Ej.: MED-001
            $table->string('registro_profesional', 30)->unique();   // Matrícula
            $table->string('nombres', 60);
            $table->string('apellidos', 60);
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 100)->nullable();
            $table->boolean('estado')->default(true);               // Inactivo = no recibe nuevas citas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
