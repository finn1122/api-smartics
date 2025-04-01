<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        /*********************** COMPUTACIÓN (3 niveles completos) ***********************/
        $computacion = Category::create(['name' => 'Computación']);

        // 1. HARDWARE
        $hardware = Category::create(['name' => 'Hardware', 'parent_id' => $computacion->id]);

        // 1.1. Computadoras de Escritorio
        $escritorio = Category::create(['name' => 'Computadoras de Escritorio', 'parent_id' => $hardware->id]);
        Category::create(['name' => 'Gaming', 'parent_id' => $escritorio->id]);
        Category::create(['name' => 'Oficina', 'parent_id' => $escritorio->id]);
        Category::create(['name' => 'Workstations', 'parent_id' => $escritorio->id]);

        // 1.2. All-in-One
        $allInOne = Category::create(['name' => 'All-in-One', 'parent_id' => $hardware->id]);
        Category::create(['name' => 'Pantalla 21"', 'parent_id' => $allInOne->id]);
        Category::create(['name' => 'Pantalla 24"', 'parent_id' => $allInOne->id]);

        // 2. PERIFÉRICOS
        $perifericos = Category::create(['name' => 'Periféricos', 'parent_id' => $computacion->id]);

        // 2.1. Teclados
        $teclados = Category::create(['name' => 'Teclados', 'parent_id' => $perifericos->id]);
        $tecladosGamer = Category::create(['name' => 'Gamer', 'parent_id' => $teclados->id]);
        Category::create(['name' => 'Mecánicos RGB', 'parent_id' => $tecladosGamer->id]);
        Category::create(['name' => 'Low Profile', 'parent_id' => $tecladosGamer->id]);

        // 2.2. Mouse
        $mouse = Category::create(['name' => 'Mouse', 'parent_id' => $perifericos->id]);
        $mouseGamer = Category::create(['name' => 'Gamer', 'parent_id' => $mouse->id]);
        Category::create(['name' => 'Alta DPI (16,000+)', 'parent_id' => $mouseGamer->id]);
        Category::create(['name' => 'Inalámbricos', 'parent_id' => $mouseGamer->id]);

        // 3. COMPONENTES
        $componentes = Category::create(['name' => 'Componentes', 'parent_id' => $computacion->id]);

        // 3.1. Procesadores
        $procesadores = Category::create(['name' => 'Procesadores', 'parent_id' => $componentes->id]);
        $intel = Category::create(['name' => 'Intel', 'parent_id' => $procesadores->id]);
        Category::create(['name' => 'Core i9', 'parent_id' => $intel->id]);
        Category::create(['name' => 'Core i7', 'parent_id' => $intel->id]);

        // 3.2. Tarjetas Gráficas
        $gpus = Category::create(['name' => 'Tarjetas Gráficas', 'parent_id' => $componentes->id]);
        $nvidia = Category::create(['name' => 'NVIDIA', 'parent_id' => $gpus->id]);
        Category::create(['name' => 'RTX 40 Series', 'parent_id' => $nvidia->id]);
        Category::create(['name' => 'RTX 30 Series', 'parent_id' => $nvidia->id]);

        /*********************** OTRAS CATEGORÍAS PRINCIPALES ***********************/

        // ELECTRÓNICA
        $electronica = Category::create(['name' => 'Electrónica']);
        $tv = Category::create(['name' => 'Televisores', 'parent_id' => $electronica->id]);
        Category::create(['name' => 'Smart TV 4K', 'parent_id' => $tv->id]);

        // CELULARES
        $celulares = Category::create(['name' => 'Celulares']);
        $samsung = Category::create(['name' => 'Samsung', 'parent_id' => $celulares->id]);
        Category::create(['name' => 'Galaxy S23', 'parent_id' => $samsung->id]);

        // GAMING
        $gaming = Category::create(['name' => 'Gaming']);
        $consolas = Category::create(['name' => 'Consolas', 'parent_id' => $gaming->id]);
        Category::create(['name' => 'PlayStation 5', 'parent_id' => $consolas->id]);

        // OFICINA
        $oficina = Category::create(['name' => 'Oficina']);
        $impresoras = Category::create(['name' => 'Impresoras', 'parent_id' => $oficina->id]);
        Category::create(['name' => 'Multifuncionales', 'parent_id' => $impresoras->id]);
    }
}
