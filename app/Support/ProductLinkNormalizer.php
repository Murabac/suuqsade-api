<?php

namespace App\Support;

class ProductLinkNormalizer
{
    public static function normalize(string $input): string
    {
        $trimmed = trim($input);

        if (preg_match('/https?:\/\/[^\s<>"\]]+/i', $trimmed, $matches)) {
            return rtrim($matches[0], ').,!?]');
        }

        return $trimmed;
    }
}
