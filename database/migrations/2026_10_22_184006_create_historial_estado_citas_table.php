<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estado_citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();       // composición
            $table->foreignId('estado_anterior_id')->nullable()->constrained('estado_citas'); // nulo en el primer registro
            $table->foreignId('estado_nuevo_id')->constrained('estado_citas');
            $table->foreignId('user_id')->constrained('users');                          // realizado por
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->string('observacion', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estado_citas');
    }
};
