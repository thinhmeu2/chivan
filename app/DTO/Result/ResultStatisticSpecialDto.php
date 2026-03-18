<?php

namespace App\DTO\Result;

use App\DTO\BaseDto;
use App\Models\Result;

class ResultStatisticSpecialDto extends BaseDto
{
    public string $number;
    public string $draw_date;
    protected function __construct(Result $item)
    {
        $this->number = $item->result_details->first()->number;
        $this->draw_date = $item->getRawOriginal('draw_date');
    }
}
