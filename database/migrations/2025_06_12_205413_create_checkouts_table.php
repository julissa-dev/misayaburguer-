<?php

// database/migrations/xxxx_xx_xx_create_checkouts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckoutsTable extends Migration
{
    public function up()
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente', 'completado', 'cancelado'])->default('pendiente');
            $table->timestamp('expira_en')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('checkouts');
    }
}
