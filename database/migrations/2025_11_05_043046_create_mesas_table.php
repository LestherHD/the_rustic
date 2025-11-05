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
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',100);
            $table->integer('pos_x');
            $table->integer('pos_y');
            $table->integer('capacidad')->default(4);
            $table->enum('estado',['libre','en_atencion','ocupada','cerrada'])->default('libre');
            $table->boolean('')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
