<?php

namespace ClassSoiCau;

class RbkKhung1 extends Base
{
    protected $type = 'rbkkhung1';

    protected function setNewNumber(): void
    {
        $stl = getRandomSTL('', ',');
        $stl = explode(',', $stl);
        array_splice($stl, 0, 0, getRandomNumber(2, 1));
        $this->number = array_map('strval', $stl);
    }
}