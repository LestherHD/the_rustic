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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_movimiento',['entrada','salida']);
            $table->decimal('cantidad',8,2);
            $table->string('referencia_tipo',50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable(); //DUDOTA
            $table->text('comentario')->nullable();
            $table->timestamps('fecha_movimiento')->useCurrent();
            $table->foreign('ingrediente_id')->references('id')->on('ingredientes')->onDelete('cascade');//DUDA
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
