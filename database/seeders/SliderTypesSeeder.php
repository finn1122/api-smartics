<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SliderTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $types = [
            [
                'name' => 'discount',
                'display_name' => 'Descuento',
                'color' => '#FF5252',
                'icon' => 'fa-percent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'offer',
                'display_name' => 'Oferta',
                'color' => '#FF9800',
                'icon' => 'fa-tag',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'news',
                'display_name' => 'Noticia',
                'color' => '#2196F3',
                'icon' => 'fa-newspaper',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('slider_types')->insert($types);

        $this->command->info('Tipos de slider creados exitosamente!');
    }
}
