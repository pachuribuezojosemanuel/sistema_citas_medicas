<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // 0..1 (LOGIN_FALLIDO sin usuario)
            $table->string('accion', 50);                  // LOGIN, LOGIN_FALLIDO, REGISTRAR, MODIFICAR...
            $table->string('modulo', 50);
            $table->text('descripcion')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('fecha_hora')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
