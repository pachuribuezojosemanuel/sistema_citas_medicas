<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alergias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();        // Ej.: Penicilina
            $table->string('descripcion', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alergias');
    }
};
