<?php

namespace App\View\Components;

use App\Helpers\CollectionHelper;
use App\Helpers\DateHelper;
use App\Services\FeServices\CategoryService;
use App\Services\FeServices\KeyService;
use Illuminate\Support\Facades\Http;
use Illuminate\View\Component;
use Symfony\Component\DomCrawler\Crawler;

abstract class BaseEmbed extends Component
{
    final public function __construct()
    {

    }
    abstract protected function getKey(): string;
    abstract protected function logicGetData(): array;

    /**
     * Get the view / contents that represent the component.
     */
    final public function render(): string
    {
        $keyService = resolve(KeyService::class);
        $viewName = $this->getKey();
        $today = DateHelper::today();
        $html = $keyService->getHtmlFromDb($viewName, $today);
        if (! $html){
            $html = $this->view("components.$viewName", $this->logicGetData());
            $keyService->saveHtml($viewName, $today, $html);
        }
        return $html;
    }
    final public function crawler(string $url): Crawler
    {
        $response = Http::get($url);

        // có thể thêm check fail cho đỡ ngu người lúc debug
        if (!$response->successful()) {
            throw new \RuntimeException("Failed to fetch: {$url}");
        }

        return new Crawler($response->body());
    }
    final protected function getTodayCategories(string $parentCode): array
    {
        $parentCode = strtoupper($parentCode);
        if (! in_array($parentCode, ['XSMT', 'XSMN']))
            throw new \LogicException("getTodayCategories valid if \$parentCode is 'XSMT' or 'XSMN'");
        $todayCategories = resolve(CategoryService::class)->todayCategories();
        $todayCategories = CollectionHelper::buildTree($todayCategories);
        $todayCategories = $todayCategories->first(fn($i) => $i->code == 'XSMN')->children;
        return $todayCategories->pluck('name')->toArray();
    }
}
