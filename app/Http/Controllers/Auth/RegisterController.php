<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\Email\EmailController;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{

    protected $emailController;

    public function __construct(EmailController $emailController)
    {
        $this->emailController = $emailController;
    }

    public function register(Request $request): JsonResponse
    {
        Log::info('register');
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => false, // Usuario inactivo hasta verificar email
        ]);

        // Generar URL de verificación
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Enviar correo de verificación
        try {
            $emailResult = $this->emailController->sendVerificationEmail($user, $verificationUrl);

            if (!$emailResult['success']) {
                return response()->json([
                    'message' => 'Usuario registrado pero hubo un problema al enviar el correo de verificación.',
                    'user' => $user,
                    'email_error' => $emailResult['message']
                ], 201);
            }

            event(new Registered($user));
            Log::debug('Usuario registrado exitosamente');

            return response()->json([
                'message' => 'Usuario registrado exitosamente. Por favor verifica tu email.',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error en registro de usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al procesar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (!hash_equals(
            (string) $request->route('hash'),
            sha1($user->getEmailForVerification())
        )) {
            return response()->json([
                'message' => 'El enlace de verificación no es válido'
            ], 400);
        }

        // Verificar si el usuario ya ha verificado su correo electrónico
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo electrónico ya ha sido verificado previamente.'
            ], 200); // Cambiar a código 200 ya que la verificación fue exitosa.
        }

        // Marcar el correo electrónico como verificado
        if ($user->markEmailAsVerified()) {
            $user->active = true; // Marcar al usuario como activo
            $user->save();
            event(new Verified($user));

            // Enviar correo de bienvenida después de verificar
            $this->emailController->sendWelcomeEmail($user);
        }

        return response()->json([
            'message' => 'Correo electrónico verificado exitosamente'
        ], 200);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        Log::info('resendVerification');
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No se encontró un usuario con ese correo electrónico'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo electrónico ya ha sido verificado'
            ], 400);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        try {
            $emailResult = $this->emailController->sendVerificationEmail($user, $verificationUrl);

            if (!$emailResult['success']) {
                return response()->json([
                    'message' => 'Error al enviar el correo de verificación',
                    'error' => $emailResult['message']
                ], 500);
            }

            return response()->json([
                'message' => 'Se ha enviado un nuevo enlace de verificación'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al enviar el correo de verificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
