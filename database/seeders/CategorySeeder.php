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
        // Crear categorías principales
        $audio = Category::create(['name' => 'Audio']);
        $perifericos = Category::create(['name' => 'Periféricos']);

        // Crear subcategorías para Audio
        $auriculares = Category::create(['name' => 'Auriculares', 'parent_id' => $audio->id]);
        $almbricos = Category::create(['name' => 'Alámbricos', 'parent_id' => $auriculares->id]);
        $inalambricos = Category::create(['name' => 'Inalámbricos', 'parent_id' => $auriculares->id]);

        // Crear subcategorías para Periféricos
        $teclados = Category::create(['name' => 'Teclados', 'parent_id' => $perifericos->id]);
        $mecanicos = Category::create(['name' => 'Mecánicos', 'parent_id' => $teclados->id]);
        $membrana = Category::create(['name' => 'De Membrana', 'parent_id' => $teclados->id]);

        $mouse = Category::create(['name' => 'Mouse', 'parent_id' => $perifericos->id]);
        $gamer = Category::create(['name' => 'Gamer', 'parent_id' => $mouse->id]);
        $oficina = Category::create(['name' => 'Oficina', 'parent_id' => $mouse->id]);
    }

}
