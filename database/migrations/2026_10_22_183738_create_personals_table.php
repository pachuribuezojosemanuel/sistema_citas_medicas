<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('ci', 15)->unique();
            $table->string('nombres', 60);
            $table->string('apellidos', 60);
            $table->string('telefono', 20)->nullable();
            $table->string('cargo', 50);                   // Recepcionista, Administrador...
            $table->date('fecha_ingreso')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personals');
    }
};
