<?php

namespace ClassSoiCau;

class DanDe10_3 extends Base
{
    protected $type = 'dande10sokhung3_2';
    protected $rangeDate = 3;
    protected $checkOnlySpecial = true;

    protected function setNewNumber(): void
    {
        $this->number = explode(' - ', getRandomNumber(2, 10));
    }
}