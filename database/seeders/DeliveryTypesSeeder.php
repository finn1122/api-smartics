<?php

namespace Database\Seeders;

use App\Models\DeliveryType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryMethods = [
            [
                'name' => 'Recogida en tienda',
                'key' => 'pickup',
                'description' => 'Recoge tu pedido en nuestra tienda física',
                'price' => 0,
                'is_free' => true,
                'estimated_days_min' => null,
                'estimated_days_max' => null,
                'active' => true,
                'sort_order' => 1,
                'metadata' => [
                    'location' => 'Calle Principal 123',
                    'hours' => 'Lunes a Viernes de 9:00 a 18:00',
                    'instructions' => 'Presentar identificación al recoger'
                ]
            ],
            [
                'name' => 'Envío estándar',
                'key' => 'standard',
                'description' => 'Entrega en 3-5 días laborables',
                'price' => 4.99,
                'is_free' => false,
                'estimated_days_min' => 3,
                'estimated_days_max' => 5,
                'active' => true,
                'sort_order' => 2,
                'metadata' => [
                    'carrier' => 'Correos Express',
                    'tracking' => true
                ]
            ],
            [
                'name' => 'Envío express',
                'key' => 'express',
                'description' => 'Entrega en 24 horas',
                'price' => 9.99,
                'is_free' => false,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'active' => true,
                'sort_order' => 3,
                'metadata' => [
                    'carrier' => 'Mensajería Urgente',
                    'tracking' => true,
                    'guaranteed' => true
                ]
            ]
        ];

        foreach ($deliveryMethods as $method) {
            DeliveryType::firstOrCreate(
                ['key' => $method['key']], // Buscar por clave única
                $method // Datos a insertar si no existe
            );
        }

        $this->command->info('Métodos de entrega creados exitosamente!');
    }
}
