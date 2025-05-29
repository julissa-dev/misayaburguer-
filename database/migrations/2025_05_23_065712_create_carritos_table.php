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
        Schema::create('carritos', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade'); // usuario_id: int(11)
            $table->timestamps(); // creado_en: datetime
            // No se especifica updated_at, solo creado_en.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carritos');
    }
};
