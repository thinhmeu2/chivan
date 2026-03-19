<?php

namespace App\View\Components;

use App\Helpers\RandomHelper;

class SoiCauVietXsmb extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-viet-xsmb';
    }

    protected function logicGetData(): array
    {
        $xsmb = [
            RandomHelper::getRandomNumber(2,1),
            RandomHelper::getRandomSTL(1),
            RandomHelper::getRandomLokep(2),
            RandomHelper::getRandomNumber(1,2),
        ];
        return [
            'xsmb' => $xsmb,
        ];
    }
}
