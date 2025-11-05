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
            $table->id();
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('estado', ['abierto', 'en_preparacion','entregado','pagado','cancelado'])->default('abierto');
            $table->foreignId('mesa_id')->constrained()->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained()->onDelete('cascade'); //duda
            $table->timestamps();
            $table->softDeletes();
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
