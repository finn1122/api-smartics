<?php

namespace App\Http\Controllers\Api\V1\Email;

use App\Http\Controllers\Controller;
use App\Services\SendinblueService;
use App\Models\User;

class EmailController extends Controller
{
    protected $sendinblueService;

    public function __construct(SendinblueService $sendinblueService)
    {
        $this->sendinblueService = $sendinblueService;
    }
    /**
     * Enviar correo de bienvenida.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendWelcomeEmail(User $user): array
    {
        try {
            $htmlContent = view('emails.welcome', [
                'userName' => $user->name
            ])->render();

            $result = $this->sendinblueService->sendEmail(
                [['email' => $user->email, 'name' => $user->name]],
                'Bienvenido a ' . config('app.name'),
                $htmlContent
            );

            return [
                'success' => true,
                'message' => 'Correo de bienvenida enviado exitosamente',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al enviar el correo de bienvenida: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Enviar correo de verificación de cuenta.
     *
     * @param User $user
     * @param string $verificationUrl
     * @return array
     */
    public function sendVerificationEmail(User $user, string $verificationUrl): array
    {
        try {
            $htmlContent = view('emails.verify', [
                'userName' => $user->name,
                'verificationUrl' => $verificationUrl
            ])->render();

            $result = $this->sendinblueService->sendEmail(
                [['email' => $user->email, 'name' => $user->name]],
                'Verifica tu cuenta en ' . config('app.name'),
                $htmlContent
            );

            return [
                'success' => true,
                'message' => 'Correo de verificación enviado exitosamente',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al enviar el correo de verificación: ' . $e->getMessage()
            ];
        }
    }
}
