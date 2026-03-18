<?php
namespace App\Services\FeServices;

use App\Models\PostCategory;
use App\SelectColumns\PostCategory\FeSelectDetailPostCategory;

class PostCategoryService extends BaseService
{
    protected string $applierList = FeSelectDetailPostCategory::class;
    protected string $applierDetail = FeSelectDetailPostCategory::class;

    public function __construct(){
        parent::__construct(resolve(PostCategory::class));
    }
}
