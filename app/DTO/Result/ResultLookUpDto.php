<?php

namespace App\DTO\Result;

use App\DTO\BaseDto;
use App\Models\Result;
use Carbon\Carbon;

class ResultLookUpDto extends BaseDto
{
    public string $code;
    public string $name;
    public Carbon $draw_date;
    public array $results;

    protected function __construct(Result $item)
    {
        $this->draw_date = $item->draw_date;
        $this->code = $item->category->code;
        $this->name = $item->category->name;
        $item->result_details->each(fn($i) => $this->results[] = [
            'prize_code' => $i->prize_code,
            'number' => $i->number,
        ]);
    }
}
