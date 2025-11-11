<?php

namespace App\Helpers;

class PriceHelper
{
    public static function parsePrice($priceString)
    {
        if (!$priceString) return [null, null];

        $parts = preg_split('/đ/', $priceString);

        $clean = fn($x) => (int) str_replace('.', '', trim($x));

        if (count($parts) >= 2 && trim($parts[1]) !== '') {
            return [
                $clean($parts[0]),
                $clean($parts[1])
            ];
        }

        return [null, $clean($parts[0])];
    }
}
