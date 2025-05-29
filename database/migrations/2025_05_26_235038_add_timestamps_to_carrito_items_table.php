<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carrito_items', function (Blueprint $table) {
            // Esto añade las columnas created_at y updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrito_items', function (Blueprint $table) {
            // Para revertir, elimina las columnas
            $table->dropTimestamps();
        });
    }
};