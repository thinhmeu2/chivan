<?php

namespace App\Keys;

use App\Helpers\RandomHelper;

class DanDe10_3 extends Base
{
    protected string $key = 'dande10sokhung3';
    protected int $range_day = 3;
    protected bool $checkOnlySpecial = true;

    protected function setNewNumber(): void
    {
        $this->number = RandomHelper::getRandomNumber(2, 10);
    }
}
