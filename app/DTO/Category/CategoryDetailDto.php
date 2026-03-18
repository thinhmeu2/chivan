<?php

namespace App\DTO\Category;

use App\Models\Category;

class CategoryDetailDto extends BaseCategoryDto
{
    public ?string $content;
    protected function __construct(Category $item)
    {
        parent::__construct($item);
        $this->content = $this->formatContentBeforeShow($item->content);
    }
}
