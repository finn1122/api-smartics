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
        // Tabla de estados (para normalización)
        Schema::create('inegi_states', function (Blueprint $table) {
            $table->string('c_estado', 2)->primary()->comment('Clave INEGI de estado');
            $table->string('d_estado', 50)->comment('Nombre del estado');
            $table->timestamps();
        });

        // Tabla de municipios (para normalización)
        Schema::create('inegi_municipalities', function (Blueprint $table) {
            $table->string('c_mnpio', 3)->comment('Clave municipal (sin estado)');
            $table->string('c_estado', 2)->comment('Clave de estado');
            $table->string('D_mnpio', 100)->comment('Nombre del municipio');
            $table->primary(['c_estado', 'c_mnpio']);
            $table->timestamps();

            $table->foreign('c_estado')->references('c_estado')->on('inegi_states');
        });

        // Tabla de ciudades (para normalización)
        Schema::create('inegi_cities', function (Blueprint $table) {
            $table->string('c_cve_ciudad', 2)->primary()->comment('Clave de ciudad');
            $table->string('d_ciudad', 100)->comment('Nombre de la ciudad');
            $table->timestamps();
        });

        // Tabla principal de asentamientos (estructura exacta del INEGI)
        Schema::create('inegi_postal_data', function (Blueprint $table) {
            // Datos principales
            $table->string('d_codigo', 5)->comment('Código Postal');
            $table->string('d_asenta', 100)->comment('Nombre del asentamiento');
            $table->string('d_tipo_asenta', 50)->comment('Tipo de asentamiento');

            // Relaciones geográficas (texto)
            $table->string('D_mnpio', 100)->comment('Municipio');
            $table->string('d_estado', 50)->comment('Estado');
            $table->string('d_ciudad', 100)->nullable()->comment('Ciudad');

            // Códigos y claves
            $table->string('d_CP', 5)->nullable()->comment('Código Postal administrativo');
            $table->string('c_estado', 2)->comment('Clave estado');
            $table->string('c_oficina', 5)->nullable()->comment('Código de oficina postal');
            $table->string('c_CP', 5)->nullable()->comment('Código Postal (alternativo)');
            $table->string('c_tipo_asenta', 2)->comment('Clave tipo asentamiento');
            $table->string('c_mnpio', 3)->comment('Clave municipal');
            $table->string('id_asenta_cpcons', 4)->comment('ID único de asentamiento');
            $table->string('d_zona', 20)->comment('Tipo de zona');
            $table->string('c_cve_ciudad', 2)->nullable()->comment('Clave ciudad');

            // Campos de control
            $table->timestamps();

            // Índices compuestos para búsquedas rápidas
            $table->index(['d_codigo', 'd_asenta']);
            $table->index(['c_estado', 'c_mnpio']);
            $table->index('id_asenta_cpcons');

            // Claves foráneas
            $table->foreign(['c_estado', 'c_mnpio'])
                ->references(['c_estado', 'c_mnpio'])
                ->on('inegi_municipalities');

            $table->foreign('c_cve_ciudad')
                ->references('c_cve_ciudad')
                ->on('inegi_cities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inegi_postal_data');
        Schema::dropIfExists('inegi_municipalities');
        Schema::dropIfExists('inegi_cities');
        Schema::dropIfExists('inegi_states');
    }
};
