<?php
namespace App\Services\FeServices;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Url;
use App\SelectColumns\Category\FeSelectDetailCategory;
use App\SelectColumns\Page\FeSelectDetailPage;
use App\SelectColumns\Post\FeSelectDetailPost;
use App\SelectColumns\PostCategory\FeSelectDetailPostCategory;
use App\SelectColumns\Url\FeSelectDetailUrl;

class UrlService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Url::class));
    }
    public function handleSlug(string $slug): Url
    {
        $q = FeSelectDetailUrl::apply($this->newQuery());
        $url = $q->where('slug', $slug)
            ->firstOrFail();
        $this->loadModelDetail($url);
        return $url;
    }

    private function loadModelDetail(Url $url): void
    {
        $applier = match ($url->model_type){
            Category::class => FeSelectDetailCategory::class,
            PostCategory::class => FeSelectDetailPostCategory::class,
            Post::class => FeSelectDetailPost::class,
            Page::class => FeSelectDetailPage::class,
            default => throw new \RuntimeException('Cần cấu hình')
        };
        $url->load([
            'model' => function ($q) use ($applier) {
                return $applier::apply($q);
            }
        ]);
    }
}
