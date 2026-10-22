<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancelacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->unique()->constrained('citas')->cascadeOnDelete(); // 0..1 por cita
            $table->foreignId('user_id')->constrained('users');                              // registrada por
            $table->string('motivo', 200);
            $table->timestamp('fecha_cancelacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancelacions');
    }
};
