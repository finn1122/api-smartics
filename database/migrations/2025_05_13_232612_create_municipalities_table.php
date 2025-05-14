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

        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained('states');
            $table->string('name');
            $table->string('c_municipio', 10)->unique()->comment('Clave única del municipio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
