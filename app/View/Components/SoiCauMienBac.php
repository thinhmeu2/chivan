<?php

namespace App\View\Components;

use App\Helpers\DateHelper;
use App\Helpers\RandomHelper;

class SoiCauMienBac extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-mien-bac';
    }

    protected function logicGetData(): array
    {
        return [
            'data' => [
                implode(' - ', RandomHelper::getRandomNumber(1,2)),
                implode(' - ', RandomHelper::getRandomNumber(2,3)),
                implode(' - ', RandomHelper::getRandomNumber(2,4)),
                implode(' - ', RandomHelper::getRandomLokep(1)),
                implode(' - ', RandomHelper::getRandomNumber(3,2)),
                implode(', ', RandomHelper::getRandomNumber(2,10)),
                implode(', ', RandomHelper::getRandomNumber(2,20)),
                implode(', ', RandomHelper::getRandomNumber(2,36)),
            ],
        ];
    }
}
