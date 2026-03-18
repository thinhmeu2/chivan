<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Http\Requests\Be\CategoryRequest;
use App\Models\BaseModel;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService extends BaseService
{
    public Collection $categories;

    public function __construct()
    {
        parent::__construct(resolve(Category::class));

        $this->categories = Cache::remember('categoriesBe', 5, function () {
            return $this->builder()
                ->get();
        });
    }
    public function whereIn($column, array $values)
    {
        return $this->categories->whereIn($column, $values)->values();
    }

    public function recursiveParentIds(Category $category): array
    {
        $result = collect([$category->id]);

        $current = $category;

        while ($current->parent_id) {
            $parent = $this->categories->firstWhere('id', $current->parent_id);

            if ($parent) {
                $result->push($parent->id);
                $current = $parent;
            } else {
                break;
            }
        }

        return $result->unique()->values()->all();
    }

    public function saveByTreeNested(array $data): void
    {
        $this->saveTrees($data, $this->categories);
    }

    public function trees(): \Illuminate\Support\Collection
    {
        return $this->buildTreeFromCollection($this->categories, 'parent_id');
    }

    protected function handleBeforeDelete(Category|BaseModel $model)
    {
        $model->children()->update(['parent_id' => 0]);
    }

    protected function handleAfterCreate(BaseModel|Category $model, BaseBeRequest|CategoryRequest $data)
    {

    }

    protected function handleBeforeUpdate(BaseModel $model, BaseBeRequest $data)
    {

    }
}
