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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // Esto crea un BIGINT UNSIGNED

            $table->string('name');
            $table->string('image_url')->nullable();
            $table->string('path')->nullable();
            $table->boolean('top')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Usar nestedSet con el mismo tipo que id
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('_lft')->default(0);
            $table->unsignedInteger('_rgt')->default(0);

            // Definir la clave foránea antes de nestedSet
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Eliminar la clave foránea primero
            $table->dropForeign(['parent_id']);

            // Luego eliminar las columnas
            $table->dropColumn(['parent_id', '_lft', '_rgt']);
        });

        Schema::dropIfExists('categories');
    }
};
