<?php
namespace App\Services\CrawlerServices;

use App\Models\Category;

class CategoryService extends BaseService
{
    public function __construct(){
        parent::__construct(resolve(Category::class));
    }
    public function searchCategoryCrawl(string $name): ?Category
    {
        return $this->newQuery()
            ->select('id', 'code')
            ->whereRaw('INSTR(keyword, ?) > 0', [$name])
            ->first();
    }
}
