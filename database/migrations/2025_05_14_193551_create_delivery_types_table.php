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
        Schema::create('delivery_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del método de entrega (ej. "Recogida en tienda")
            $table->string('key')->unique(); // Clave única para identificar el tipo (ej. "pickup")
            $table->text('description')->nullable(); // Descripción opcional para mostrar al cliente
            $table->decimal('price', 10, 2)->default(0); // Costo del envío
            $table->boolean('is_free')->default(false); // ¿Es gratuito?
            $table->integer('estimated_days_min')->nullable(); // Días estimados mínimos de entrega
            $table->integer('estimated_days_max')->nullable(); // Días estimados máximos de entrega
            $table->boolean('active')->default(true); // Para activar/desactivar el método
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->json('metadata')->nullable(); // Datos adicionales en formato JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_types');
    }
};
