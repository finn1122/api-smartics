<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSupplierSeeder extends Seeder
{
    public function run()
    {
        // Insertar un usuario
        $user = User::create([
            'name' => 'mauro',
            'email' => 'mauroivaning@gmail.com',
            'password' => Hash::make('123456'), // Recuerda cambiar la contraseña
        ]);

        $supplierNames = [
            'CVA',
        ];

        foreach ($supplierNames as $supplierName) {
            Supplier::create([
                'name' => $supplierName,
                'address' => '',
                'active' => true
            ]);
        }



        $this->command->info('Usuario y proveedor creados exitosamente.');
    }
}
