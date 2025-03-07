<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {{ config('app.name') }}</title>
    <style>
        /* Reset de estilos para clientes de correo */
        body, table, td, div, p, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
        }

        /* Contenedor principal */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        /* Estilos del header */
        .header {
            background-color: #4f46e5;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            padding: 0;
        }

        /* Logo container */
        .logo-container {
            margin-bottom: 20px;
        }

        .logo-container img {
            max-width: 150px;
            height: auto;
        }

        /* Contenido principal */
        .content {
            padding: 40px 30px;
            background-color: #ffffff;
        }

        .content p {
            color: #374151;
            font-size: 16px;
            margin-bottom: 20px;
        }

        /* Footer */
        .footer {
            padding: 30px 20px;
            background-color: #f9fafb;
            text-align: center;
        }

        .footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #6b7280;
            text-decoration: none;
        }

        /* Estilos responsivos */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .content {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6;">
<table role="presentation" class="email-container" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td>
            <!-- Header -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="header">
                        <div class="logo-container">
                            <!-- Reemplazar con la URL de tu logo -->
                            <img src="https://via.placeholder.com/150x50" alt="{{ config('app.name') }}" />
                        </div>
                        <h1>¡Bienvenido {{ $userName }}!</h1>
                    </td>
                </tr>
            </table>

            <!-- Contenido Principal -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="content">
                        <p>¡Gracias por unirte a {{ config('app.name') }}! Estamos emocionados de tenerte con nosotros.</p>
                        <p>Tu cuenta ha sido creada exitosamente y ya puedes comenzar a disfrutar de todos nuestros servicios.</p>
                        <p>Si tienes alguna pregunta o necesitas ayuda, nuestro equipo de soporte está siempre disponible para asistirte.</p>
                    </td>
                </tr>
            </table>

            <!-- Footer -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="footer">
                        <div class="social-links">
                            <a href="#">Facebook</a>
                            <a href="#">Twitter</a>
                            <a href="#">LinkedIn</a>
                        </div>
                        <p>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
