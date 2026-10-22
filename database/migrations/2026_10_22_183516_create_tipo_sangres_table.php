<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_sangres', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 5)->unique();         // A+, A-, B+, B-, AB+, AB-, O+, O-
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_sangres');
    }
};
