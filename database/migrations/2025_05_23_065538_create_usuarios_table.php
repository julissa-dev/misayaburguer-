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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // id: int(11)
            $table->string('nombre', 100); // nombre: varchar(100)
            $table->string('apellido', 100); // apellido: varchar(100)
            $table->string('email', 100)->unique(); // email: varchar(100)
            $table->string('password', 255); // password: varchar(255)
            $table->text('direccion')->nullable(); // direccion: text (nullable si no es obligatorio al inicio)
            $table->string('telefono', 20)->nullable(); // telefono: varchar(20) (nullable si no es obligatorio al inicio)
            $table->enum('rol', ['cliente', 'admin', 'repartidor'])->default('cliente'); // rol: enum('cliente','admin','repartidor')
            $table->rememberToken();
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
