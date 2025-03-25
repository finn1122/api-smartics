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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('discount')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('button_text');
            $table->string('button_link')->default('/');
            $table->string('image_url')->nullable();
            $table->string('bg_color')->default('bg-dark');
            $table->string('promo_message');
            $table->enum('text_position', ['left', 'right'])->default('left');
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->foreignId('slider_type_id')->constrained('slider_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
