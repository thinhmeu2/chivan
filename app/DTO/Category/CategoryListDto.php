<?php

namespace App\DTO\Category;

use App\Models\Category;

class CategoryListDto extends BaseCategoryDto
{
    public array $children = [];
    protected function __construct(Category $item)
    {
        parent::__construct($item);
        if ($item->relationLoaded('children')) {
            $this->children = CategoryListDto::from($item->children);
        }
    }
}
