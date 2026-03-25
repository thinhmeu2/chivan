<?php

namespace App\Helpers;

class ResultHelper
{
    public static function getLoto(array $results): array
    {
        return array_map(function ($number) {
            return substr($number, -2);
        }, $results);
    }
}
