<?php

namespace App\DTO\Category;

use App\DTO\BaseDto;
use App\Models\Category;

abstract class BaseCategoryDto extends BaseDto
{
    public readonly int $id;
    public readonly int $parent_id;
    public readonly ?string $name;
    public readonly array $draw_dow;
    public readonly string $type;
    public readonly string $code;

    protected function __construct(Category $item)
    {
//        parent::__construct($item);
        $this->id = $item->id;
        $this->parent_id = (int) $item->parent_id;
        $this->name = e($item->pivot?->name ?: $item->name);
        $this->draw_dow = $item->draw_dow;
        $this->type = $item->type;
        $this->code = $item->code;
    }
}
