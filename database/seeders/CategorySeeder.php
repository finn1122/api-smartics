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
        $auriculares = $audio->children()->create(['name' => 'Auriculares']);
        $almbricos = $auriculares->children()->create(['name' => 'Alámbricos']);
        $inalambricos = $auriculares->children()->create(['name' => 'Inalámbricos']);

        // Crear subcategorías para Periféricos
        $teclados = $perifericos->children()->create(['name' => 'Teclados']);
        $mecanicos = $teclados->children()->create(['name' => 'Mecánicos']);
        $membrana = $teclados->children()->create(['name' => 'De Membrana']);

        $mouse = $perifericos->children()->create(['name' => 'Mouse']);
        $gamer = $mouse->children()->create(['name' => 'Gamer']);
        $oficina = $mouse->children()->create(['name' => 'Oficina']);
    }

}
