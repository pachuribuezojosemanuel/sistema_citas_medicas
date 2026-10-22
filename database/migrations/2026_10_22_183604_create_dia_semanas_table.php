<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dia_semanas', function (Blueprint $table) {
            $table->id();                                  // 1 = Lunes ... 7 = Domingo
            $table->string('nombre', 10)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dia_semanas');
    }
};
