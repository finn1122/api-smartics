<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de estados
        Schema::create('inegi_states', function (Blueprint $table) {
            $table->id(); // Nuevo ID autoincremental
            $table->string('c_estado', 2)->unique()->comment('Clave INEGI de estado');
            $table->string('d_estado', 50)->comment('Nombre del estado');
            $table->string('abrev', 10)->nullable()->comment('Abreviatura oficial');
            $table->timestamps();
        });

        // 2. Tabla de municipios
        Schema::create('inegi_municipalities', function (Blueprint $table) {
            $table->id(); // Nuevo ID autoincremental
            $table->string('c_mnpio', 3)->comment('Clave municipal');
            $table->string('c_estado', 2)->comment('Clave de estado');
            $table->string('D_mnpio', 100)->comment('Nombre del municipio');
            $table->timestamps();

            // Cambiamos a unique en lugar de primary
            $table->unique(['c_estado', 'c_mnpio']);

            $table->foreign('c_estado')->references('c_estado')->on('inegi_states');
        });

        // 3. Tabla de tipos de asentamiento
        Schema::create('inegi_settlement_types', function (Blueprint $table) {
            $table->id(); // Nuevo ID autoincremental
            $table->string('c_tipo_asenta', 2)->unique()->comment('Clave tipo asentamiento');
            $table->string('d_tipo_asenta', 50)->comment('Descripción completa');
            $table->string('short_name', 20)->comment('Abreviatura');
            $table->timestamps();
        });

        // 4. Tabla de ciudades
        Schema::create('inegi_cities', function (Blueprint $table) {
            $table->id(); // Nuevo ID autoincremental
            $table->string('c_cve_ciudad', 4)->unique()->comment('Clave INEGI de ciudad');
            $table->string('d_ciudad', 100)->comment('Nombre oficial');
            $table->string('c_estado', 2)->comment('Clave estado');
            $table->string('c_mnpio', 3)->comment('Clave municipio');
            $table->boolean('es_capital')->default(false);
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->timestamps();

            $table->foreign(['c_estado', 'c_mnpio'])
                ->references(['c_estado', 'c_mnpio'])
                ->on('inegi_municipalities');
        });

        // 5. Tabla principal de datos postales
        Schema::create('inegi_postal_data', function (Blueprint $table) {
            // Campos principales
            $table->id(); // ID autoincremental
            $table->string('d_codigo', 5)->comment('Código Postal');
            $table->string('id_asenta_cpcons', 4)->comment('ID único de asentamiento');

            // Datos descriptivos
            $table->string('d_asenta', 100)->comment('Nombre del asentamiento');
            $table->string('d_tipo_asenta', 50)->comment('Tipo de asentamiento');
            $table->string('D_mnpio', 100)->comment('Municipio');
            $table->string('d_estado', 50)->comment('Estado');
            $table->string('d_ciudad', 100)->nullable()->comment('Ciudad');
            $table->string('d_zona', 20)->comment('Zona (Urbana/Rural)');

            // Campos de códigos (sin d_CP que causaba el error)
            $table->string('c_estado', 2)->comment('Clave estado');
            $table->string('c_mnpio', 3)->comment('Clave municipio');
            $table->string('c_tipo_asenta', 2)->comment('Clave tipo asentamiento');
            $table->string('c_cve_ciudad', 2)->nullable()->comment('Clave ciudad');
            $table->string('c_oficina', 5)->nullable()->comment('Oficina postal');

            // Geo datos
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            $table->timestamps();

            // Clave única compuesta (además del ID autoincremental)
            $table->unique(['d_codigo', 'id_asenta_cpcons']);

            // Relaciones
            $table->foreign(['c_estado', 'c_mnpio'])
                ->references(['c_estado', 'c_mnpio'])
                ->on('inegi_municipalities');

            $table->foreign('c_tipo_asenta')
                ->references('c_tipo_asenta')
                ->on('inegi_settlement_types');

            $table->foreign('c_cve_ciudad')
                ->references('c_cve_ciudad')
                ->on('inegi_cities');
        });
    }

    public function down(): void
    {
        // Orden inverso para eliminar
        Schema::dropIfExists('inegi_postal_data');
        Schema::dropIfExists('inegi_cities');
        Schema::dropIfExists('inegi_settlement_types');
        Schema::dropIfExists('inegi_municipalities');
        Schema::dropIfExists('inegi_states');
    }
};
