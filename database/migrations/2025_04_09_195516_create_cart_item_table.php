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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 12, 2); // Precio en el momento de agregar
            $table->decimal('original_price', 12, 2); // Precio original de referencia
            $table->integer('quantity')->default(1);
            $table->json('options')->nullable(); // Para variantes, personalizaciones, etc.
            $table->timestamps();

            $table->unique(['cart_id', 'product_id', 'supplier_id']); // Evita duplicados
            $table->index(['product_id', 'supplier_id']); // Para búsquedas rápidas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
