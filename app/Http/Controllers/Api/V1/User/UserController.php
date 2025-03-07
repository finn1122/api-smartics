<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function getUserById($user_id)
    {
        try {
            Log::info('getUserById');
            $user = User::findOrFail($user_id);

            // Registrar y usar la Policy manualmente
            $policy = new UserPolicy();
            if (!$policy->view(auth()->user(), $user)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = New UserResource($user);

            return response()->json($user, 200);
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
