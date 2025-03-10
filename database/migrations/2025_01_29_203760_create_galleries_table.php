<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id(); // ID único de la galería
            $table->string('image_url'); // URL de la imagen
            $table->unsignedBigInteger('product_id')->nullable(); // Relación con el producto
            $table->boolean('active')->default(true);
            $table->timestamps(); // Campos created_at y updated_at

            // Clave foránea para la relación con la tabla products
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
