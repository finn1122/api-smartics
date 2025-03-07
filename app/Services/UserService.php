<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getUserProfile(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            //'roles' => $user->roles->pluck('name'), // Asumiendo que el modelo User tiene una relación roles
            // Añadir más información según sea necesario
        ];
    }
}
