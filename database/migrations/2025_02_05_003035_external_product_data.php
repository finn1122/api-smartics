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
        Schema::create('external_product_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2); // Precio ofrecido por el proveedor
            $table->decimal('sale_price', 10, 2);
            $table->string('currency_code');
            $table->integer('quantity'); // Cantidad disponible en el proveedor
            $table->timestamp('consulted_at'); // Fecha y hora de la consulta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
