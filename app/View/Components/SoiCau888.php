<?php

namespace App\View\Components;

use App\Helpers\DateHelper;
use App\Helpers\RandomHelper;

class SoiCau888 extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau888';
    }

    protected function logicGetData(): array
    {
        return [
            'data' => [
                implode(' - ', RandomHelper::getRandomNumber(2,1)),
                implode(' - ', RandomHelper::getRandomSTL(1)[0]),
                implode(' - ', RandomHelper::getRandomNumber(3,3)),
                implode(' - ', RandomHelper::getRandomNumber(2,2)),
                implode(' - ', RandomHelper::getRandomNumber(2,3)),
                implode(' - ', RandomHelper::getRandomLokep(2)),
                implode(' - ', RandomHelper::getRandomNumber(1,2)),
            ],
        ];
    }
}
