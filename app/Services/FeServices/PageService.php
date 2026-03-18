<?php
namespace App\Services\FeServices;

use App\Enums\PageTypeEnum;
use App\Models\Page;
use App\SelectColumns\Page\FeSelectDetailPage;
use App\SelectColumns\Page\FeSelectListPage;

class PageService extends BaseService
{
    protected string $applierList = FeSelectListPage::class;
    protected string $applierDetail = FeSelectDetailPage::class;
    public function __construct()
    {
        parent::__construct(resolve(Page::class));
    }
    public function getPageStatistic(PageTypeEnum $enum, bool $isList = true): Page
    {
        $applier = $isList ? $this->applierList : $this->applierDetail;
        return $applier::apply($this->newQuery())->where('type', $enum)->firstOrFail();
    }
}
