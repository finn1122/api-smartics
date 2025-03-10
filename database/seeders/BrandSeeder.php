<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $brandNames = [
            'Adata',
            'Hisense',
            'Samsung',
            'HP',
            'Intel',
            'AMD',
            'Logitech',
            'Tp-link',
            'Kingston',
        ];

        // Recorrer el array y crear registros en la base de datos
        foreach ($brandNames as $name) {
            Brand::firstOrCreate(
                ['name' => $name], // Buscar por este campo
                [
                    'active' => true, // Todas las marcas estarán activas
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
