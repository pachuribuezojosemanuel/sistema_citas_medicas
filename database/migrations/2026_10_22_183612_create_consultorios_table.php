<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultorios', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10)->unique();        // Ej.: C-101
            $table->string('nombre', 50);                  // Ej.: Consultorio Pediatría
            $table->string('piso', 10)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultorios');
    }
};
