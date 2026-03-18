<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Models\BaseModel;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PostCategoryService extends BaseService
{
    public Collection $postCategories;
    public function __construct()
    {
        parent::__construct(resolve(PostCategory::class));
//        $this->selectList = ['id', 'name', 'type', 'slug', 'deleted_at'];
        $this->postCategories = Cache::remember('PostCategories', 5, function () {
            return $this->builder()->get();
        });
    }
    public function trees()
    {
        return $this->buildTreeFromCollection($this->postCategories);
    }
    public function saveByTreeNested(array $data): void
    {
        $this->saveTrees($data, $this->postCategories);
//        Artisan::call('app:update-post-categories-level-parent');
    }

    protected function handleBeforeDelete(BaseModel|PostCategory $model)
    {

    }

    public function collectHierarchyIds(
        int|PostCategory $category,
        bool $includeSelf = false,
        string $direction = 'up'
    ): array {
        $categoryId = $category instanceof PostCategory ? $category->id : $category;
        $ids = $includeSelf ? [$categoryId] : [];

        if ($direction === 'down') {
            $children = $this->postCategories->where('parent_id', $categoryId);
            foreach ($children as $child) {
                $ids[] = $child->id;
                $ids = array_merge($ids, $this->collectHierarchyIds($child, false, 'down'));
            }
        } elseif ($direction === 'up') {
            $current = $this->postCategories->firstWhere('id', $categoryId);
            while ($current && $current->parent_id) {
                $parent = $this->postCategories->firstWhere('id', $current->parent_id);
                if ($parent) {
                    $ids[] = $parent->id;
                    $current = $parent;
                } else {
                    break;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    protected function handleAfterCreate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleAfterCreate() method.
    }

    protected function handleBeforeUpdate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleBeforeUpdate() method.
    }
}
