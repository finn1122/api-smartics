<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class JWTAuthController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function login(Request $request)
    {
        Log::info('login');
        // Validación de la solicitud
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'rememberMe' => ['sometimes', 'boolean'], // "remember_me" es opcional y debe ser booleano
        ]);

        // Si la validación falla, devuelve los errores
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');
        $rememberMe = $request->input('rememberMe', false); // Obtén el valor de "Remember Me"

        try {
            // Configurar el tiempo de vida del token JWT
            $ttl = $rememberMe ? 7 * 24 * 60 : 60; // 7 días o 1 hora (en minutos)
            JWTAuth::factory()->setTTL($ttl); // Establece el tiempo de vida del token

            // Intentar autenticar al usuario
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();
            Log::debug($user);

            if (!$user->hasVerifiedEmail()) {
                return response()->json(['error' => 'Email not verified'], 403);
            }

            if (!$user->active) {
                return response()->json(['error' => 'User account is inactive'], 403);
            }

            // Guardar el token en una cookie HTTP segura
            $cookieExpiration = $rememberMe ? 7 * 24 * 60 : 60; // 7 días o 1 hora
            $cookie = Cookie::make('jwt_token', $token, $cookieExpiration, '/', null, false, true);

            // Formatear respuesta
            $user = New UserResource($user);

            Log::debug('login success');
            return response()->json([
                'message' => 'Login successful',
                'authenticated' => true,
                'user' => $user,
                'token' => $token, // Opcional: Devuelve el token en la respuesta
            ]); // Adjuntar cookie a la respuesta

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }    // Get authenticated user
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    // User logout
    public function logout()
    {
        try {
            Log::info('logout');
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'success']);
        }catch (\Exception $e){
            Log::error($e);
            return response()->json($e->getMessage(), $e->getStatusCode());
        }
    }
}
