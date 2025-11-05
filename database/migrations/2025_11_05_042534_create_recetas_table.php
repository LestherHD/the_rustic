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
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('version')->default(1);
            $table->text('observaciones')->nullable();
            $table->bolean('activo')->default(true);
            $table->foreignId('plato_id')->nullable()->constrained()->onDelete('cascade'); //DUDA DEL ONDELETE
            $table->bolean('activo')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
