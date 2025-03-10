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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Relación con el producto
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade'); // Relación con el proveedor
            $table->integer('quantity'); // Cantidad de productos en el lote
            $table->decimal('purchase_price', 10, 2); // Precio de compra por unidad
            $table->decimal('sale_price', 10, 2); // Precio de venta por unidad
            $table->date('purchase_date'); // Fecha de compra del lote
            $table->string('purchase_document_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
