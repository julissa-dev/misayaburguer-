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
        Schema::create('promociones', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->string('nombre', 100); // nombre: varchar(100)
            $table->text('descripcion')->nullable(); // descripcion: text
            $table->decimal('precio_promocional', 10, 2); // precio_promocional: decimal(10,2)
            $table->string('imagen_url', 255)->nullable(); // imagen_url: varchar(255)
            $table->boolean('activa')->default(true); // activa: tinyint(1)
            // No se especifica created_at/updated_at en el diagrama.
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
