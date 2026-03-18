<?php

namespace App\Helpers;

class ResultHelper
{
    public static function getLoto(array $results): array
    {
        unset($results['madb']);
        $lotos = array_pad([], 10, []);
        foreach ($results as $numbers) {
            foreach ($numbers as $number) {
                $loto = substr($number, -2);
                $head = substr($loto,  0, 1);
                $lotos[$head][] = $loto;
            }
        }
        return $lotos;
    }

    public static function isSpinning(array $data): bool
    {
        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (in_array(null, $row, true)) {
                return true;
            }
        }

        return false;
    }
}
