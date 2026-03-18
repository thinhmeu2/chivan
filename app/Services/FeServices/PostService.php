<?php
namespace App\Services\FeServices;

use App\Models\Post;
use App\Models\PostCategory;
use App\SelectColumns\Post\FeSelectDetailPost;
use App\SelectColumns\Post\FeSelectListPost;
use Illuminate\Database\Eloquent\Collection;

class PostService extends BaseService
{
    protected string $applierList = FeSelectListPost::class;
    protected string $applierDetail = FeSelectDetailPost::class;

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function getByPostCategory(PostCategory $postCategory, int $limit = 4, int $page = 1): ?Collection
    {
        return $this->applierList::apply($postCategory->posts())
            ->forPage($page, $limit)
            ->get();
    }
}
