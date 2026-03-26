<?php

namespace App\View\Components;

use App\Helpers\DateHelper;
use App\Helpers\RandomHelper;

class SoiCauRongBachKim extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-rong-bach-kim';
    }

    protected function logicGetData(): array
    {
        $td3 = RandomHelper::getRandomNumber(2,6);
        $tmpTd3 = '';
        for ($i = 0; $i < count($td3); $i += 2) {
            $tmpTd3 .= '(' . $td3[$i] . ' - ' . $td3[$i + 1] . ') ';
        }

        $td5 = RandomHelper::getRandomNumber(1,2);
        $tmpTd5 = "Đầu $td5[0] - Đuôi $td5[1]";

        return [
            'data' => [
                implode(' - ', RandomHelper::getRandomNumber(2,1)),
                implode(' - ', RandomHelper::getRandomSTL(1)[0]),
                $tmpTd3,
                implode(' - ', RandomHelper::getRandomLokep(2)),
                $tmpTd5,
                implode(' - ', RandomHelper::getRandomNumber(3,4)),
            ],
        ];
    }
}
