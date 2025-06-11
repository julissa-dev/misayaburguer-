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
        Schema::create('promocion_detalle', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade'); // promo_id: int(11)
            $table->foreignId('producto_id')->constrained('productos'); // producto_id: int(11)
            $table->integer('cantidad'); // cantidad: int(11)
            $table->timestamps();
            // No se especifican timestamps en el diagrama.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_detalle');
    }
};
