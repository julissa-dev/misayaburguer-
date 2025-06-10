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
        Schema::create('productos', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->string('nombre', 100); // nombre: varchar(100)
            $table->text('descripcion')->nullable(); // descripcion: text
            $table->decimal('precio', 10, 2); // precio: decimal(10,2)
            $table->string('imagen_url', 255)->nullable(); // imagen_url: varchar(255)
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->boolean('disponible')->default(true); // disponible: tinyint(1)
            $table->timestamps();
            
            // No se especifica created_at/updated_at en el diagrama, pero es buena práctica incluirlos si se gestiona el producto.
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
