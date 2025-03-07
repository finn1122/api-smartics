<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;  // Define una propiedad para los datos

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;  // Asigna los datos recibidos a la propiedad
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.welcome')  // Define la vista del correo
        ->subject('Bienvenido a Nuestra Aplicación')
            ->with([
                'userName' => $this->user->name,
            ]);
    }
}
