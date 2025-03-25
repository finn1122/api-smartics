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
        Schema::create('slider_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // discount, launch, news, etc.
            $table->string('display_name'); // Descuento, Lanzamiento, Noticia, etc.
            $table->string('color')->default('#000000'); // Color para estilos
            $table->string('icon')->nullable(); // Ícono opcional
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slider_types');
    }
};
