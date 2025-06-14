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
        Schema::create('envios', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade'); // pedido_id: int(11)
            $table->foreignId('repartidor_id')->nullable()->constrained('usuarios'); // repartidor_id: int(11) (nullable si se asigna después)
            $table->enum('estado', ['asignado', 'en ruta', 'entregado'])->default('asignado'); // estado: enum
            $table->dateTime('actualizado_en')->useCurrent()->useCurrentOnUpdate(); // actualizado_en: datetime
            // No se especifica created_at. actualizado_en puede ser manejado por timestamps().
            // Si quieres ambos timestamps(), puedes reemplazar actualizado_en con $table->timestamps();
            $table->timestamps(); // Esto añade created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
