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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade'); // pedido_id: int(11)
            $table->string('metodo', 50); // metodo: varchar(50)
            $table->enum('estado', ['pendiente', 'pagado', 'fallido'])->default('pendiente'); // estado: enum
            $table->dateTime('fecha')->useCurrent(); // fecha: datetime (asumiendo que es la fecha del pago)
            $table->string('referencia', 100)->nullable(); // referencia: varchar(100) (ej. ID de transacción)
            // No se especifican timestamps en el diagrama.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
