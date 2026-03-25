<?php

namespace App\View\Components;

use App\Helpers\DateHelper;
use App\Helpers\RandomHelper;

class SoiCauWapXsmb extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-wap-xsmb';
    }

    protected function logicGetData(): array
    {
        return [
            'data' => [
                implode(' - ', RandomHelper::getRandomNumber(2,1)),
                implode(' - ', RandomHelper::getRandomSTL(1)[0]),
                implode(' - ', RandomHelper::getRandomNumber(2,2)),
                implode(' - ', RandomHelper::getRandomNumber(2,3)),
                implode(' - ', RandomHelper::getRandomLokep(2)),
                implode(' - ', RandomHelper::getRandomNumber(2,2)),
                implode(' - ', RandomHelper::getRandomNumber(3,3)),
            ],
        ];
    }
}
