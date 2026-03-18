<?php

namespace App\DTO\Category;

use App\DTO\BaseDto;
use App\Models\Category;

class CategorySelectOptionDto extends BaseDto
{
    public string $name;
    public string $code;
    protected function __construct(Category $item)
    {
        $this->name = $item->name;
        $this->code = strtolower($item->code);
    }
}
