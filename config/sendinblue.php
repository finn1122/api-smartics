<?php

return [
    'api_key' => env('SENDINBLUE_API_KEY'),
    'default_from_email' => env('SENDINBLUE_FROM_EMAIL', 'no-reply@tudominio.com'),
    'default_from_name' => env('SENDINBLUE_FROM_NAME', 'Tu Aplicación'),
];
