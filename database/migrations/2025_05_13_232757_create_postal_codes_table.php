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
        Schema::create('postal_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();
            $table->foreignId('municipality_id')->constrained('municipalities');
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->string('zone_type')->nullable()->comment('Tipo de zona: Urbana/Rural');
            $table->string('settlement_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postal_codes');
    }
};
