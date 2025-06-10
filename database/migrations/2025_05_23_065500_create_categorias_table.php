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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id(); // id: int(11) - Clave primaria
            $table->string('nombre', 50)->unique(); // nombre: varchar(50) - El nombre de la categoría, debe ser único
            $table->string('imagen_icono', 255)->nullable(); // Opcional: para almacenar el path a un icono de la categoría
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};