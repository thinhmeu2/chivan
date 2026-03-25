<?php

namespace App\Keys;

use App\Helpers\RandomHelper;

class DanDe20_3 extends Base
{
    protected string $key = 'dande20sokhung3';
    protected int $range_day = 3;
    protected bool $checkOnlySpecial = true;

    protected function setNewNumber(): void
    {
        $this->number = RandomHelper::getRandomNumber(2, 20);
    }
}
