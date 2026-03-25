<?php

namespace App\Helpers;

class ResultHelper
{
    public static function getLoto(array $results, int $length = 2): array
    {
        return array_map(fn($number) => substr($number, -$length), $results);
    }
}
