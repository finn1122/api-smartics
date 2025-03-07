<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu correo electrónico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background-color: #6a11cb;
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-content {
            padding: 30px 20px;
            text-align: center;
        }
        .verification-button {
            display: inline-block;
            background-color: #2575fc;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .verification-button:hover {
            transform: scale(1.05);
        }
        .email-footer {
            background-color: #f1f3f5;
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6c757d;
        }
        .disclaimer {
            color: #6c757d;
            font-size: 14px;
            margin-top: 20px;
        }
        @media screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        <h1>Verifica tu correo electrónico</h1>
    </div>
    <div class="email-content">
        <h2>¡Hola, {{ $userName }}!</h2>
        <p>Gracias por registrarte en {{ config('app.name') }}. Para completar tu registro y activar tu cuenta, haz clic en el botón de abajo:</p>

        <a href="{{ $verificationUrl }}" class="verification-button">Verificar mi correo</a>

        <div class="disclaimer">
            <p>Si no creaste esta cuenta, puedes ignorar este mensaje.</p>
            <p>Este enlace expirará en 24 horas.</p>
        </div>
    </div>
    <div class="email-footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
    </div>
</div>
</body>
</html>
