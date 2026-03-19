<?php

namespace App\View\Components;

use App\Helpers\RandomHelper;

class LoDepHomNay extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'lo-dep-hom-nay';
    }

    protected function logicGetData(): array
    {
        $xsmb = [
            RandomHelper::getRandomNumber(2,1),
            RandomHelper::getRandomSTL(1),
            RandomHelper::getRandomNumber(2,2),
            RandomHelper::getRandomNumber(2,3),
            RandomHelper::getRandomLokep(1),
            RandomHelper::getRandomNumber(2,4),
            RandomHelper::getRandomNumber(2,6),
            RandomHelper::getRandomNumber(2,10),
        ];
        return [
            'data' => $xsmb,
        ];
    }
}
