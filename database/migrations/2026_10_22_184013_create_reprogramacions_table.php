<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reprogramacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();   // composición
            $table->foreignId('user_id')->constrained('users');                      // realizada por
            $table->date('fecha_anterior');
            $table->time('hora_anterior');
            $table->date('fecha_nueva');
            $table->time('hora_nueva');
            $table->string('motivo', 200)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reprogramacions');
    }
};
