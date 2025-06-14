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
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade'); // pedido_id: int(11)
            $table->foreignId('producto_id')->constrained('productos'); // producto_id: int(11)
            $table->bigInteger('promocion_id')->unsigned()->nullable();
            $table->integer('cantidad'); // cantidad: int(11)
            $table->decimal('precio_unit', 10, 2); // precio_unit: decimal(10,2)
            $table->foreign('promocion_id')
                ->references('id')->on('promociones')
                ->onUpdate('RESTRICT')
                ->onDelete('RESTRICT');
            // No se especifican timestamps en el diagrama.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_items');
    }
};
