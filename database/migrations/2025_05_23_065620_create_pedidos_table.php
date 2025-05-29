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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->foreignId('usuario_id')->constrained('usuarios'); // usuario_id: int(11)
            $table->text('direccion'); // direccion: text
            $table->enum('estado', ['en preparacion', 'en camino', 'entregado'])->default('en preparacion'); // estado: enum
            $table->decimal('total', 10, 2); // total: decimal(10,2)
            $table->dateTime('fecha')->useCurrent(); // fecha: datetime (asumiendo que se registra al crear)
            // No se especifica created_at/updated_at en el diagrama, pero fecha podría ser equivalente a created_at.
            // Si quieres ambos, usa $table->timestamps(); y $table->dateTime('fecha')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
