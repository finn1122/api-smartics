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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // Nombre del producto
            $table->string('cva_key');
            $table->string('sku')->unique();    // SKU (código único del producto)
            $table->string('warranty')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();  // Relación con la marca
            $table->unsignedBigInteger('group_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Relación de claves foráneas
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
