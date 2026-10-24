<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un paciente puede registrarse sin cuenta de acceso al portal.
     * El índice UNIQUE se mantiene: PostgreSQL permite varios NULL.
     */
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};