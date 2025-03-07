<?php
namespace App\Utils;

class CurrencyHelper
{
    public static function getCurrencyCode(string $currency): string
    {
        $mapping = [
            'Pesos' => 'mxn',
            'Dolares' => 'usd',
            'Euros' => 'eur',
        ];

        return $mapping[$currency] ?? 'UNKNOWN';
    }
}
