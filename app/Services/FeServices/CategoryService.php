<?php
namespace App\Services\FeServices;

use App\Enums\CategoryTypeEnum;
use App\Helpers\DateHelper;
use App\Models\Category;
use App\SelectColumns\Category\FeSelectDetailCategory;
use App\SelectColumns\Category\FeSelectListCategory;
use App\SelectColumns\Url\FeSelectDetailUrl;
use App\SelectColumns\Url\FeSelectListUrl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryService extends BaseService
{
    protected string $applierList = FeSelectListCategory::class;
    protected string $applierDetail = FeSelectDetailCategory::class;

    public function __construct(){
        parent::__construct(resolve(Category::class));
    }
    public function yesterdayCategories(): Collection
    {
        return Cache::remember('yesterdayCategories', 60, function () {
            $dateNYesterday = DateHelper::dateNYesterday();
            return $this->applierList::apply($this->newQuery())
                ->whereNot('type', CategoryTypeEnum::Dayofweek)
                ->whereRaw('instr(draw_dow, ?)', $dateNYesterday)
                ->with([
                    'url' => fn($q) => FeSelectListUrl::apply($q)
                ])
                ->get();
        });
    }
    public function todayCategories(): Collection
    {
        $dateNToday = DateHelper::dateNToday();

        $typeValid = [
            CategoryTypeEnum::Region->value,
            CategoryTypeEnum::RegionVsLottery->value,
            CategoryTypeEnum::Lottery->value,
        ];

        return $this->applierList::apply($this->newQuery())
            ->whereIn('type', $typeValid)
            ->WhereDow($dateNToday)
            ->get();
    }
    public function categories(): Collection
    {
        return $this->applierList::apply($this->newQuery())
            ->whereNot('type', CategoryTypeEnum::Dayofweek)
            ->with([
                'url' => fn($q) => FeSelectListUrl::apply($q)
            ])
            ->get();
    }
    public function getCateDow(string $code, Carbon $draw_date): Category
    {
        $code = strtoupper($code);
        return $this->applierList::apply($this->newQuery())
            ->where('code', $code)
            ->where('type', CategoryTypeEnum::Dayofweek)
            ->WhereDow($draw_date->format('N'))
            ->with([
                'url' => fn($q) => FeSelectListUrl::apply($q)
            ])
            ->first();
    }
    public function vietlottCategories(): Collection
    {
        return $this->applierList::apply($this->newQuery())
            ->whereIn('code', ['MEGA', 'POWER', 'MAX3D', 'MAX3DPRO'])
            ->with([
                'url' => fn($q) => FeSelectListUrl::apply($q)
            ])
            ->get();
    }
    public function statisticCategories(?string $code = null): Category|Collection
    {
        $query = $this->applierList::apply($this->newQuery())
            ->whereNotIn('code', ['MEGA', 'POWER', 'MAX3D', 'MAX3DPRO'])
            ->whereIn('type', CategoryTypeEnum::getTypeLottery());

        if ($code) {
            return $query->where('code', $code)->firstOrFail();
        }

        return $query->get();
    }
    public function spinCategories(?string $code = null): Category|Collection
    {
        $query = $this->applierList::apply($this->newQuery())
            ->whereNotIn('code', ['VIETLOTT', 'MEGA', 'POWER', 'MAX3D', 'MAX3DPRO'])
            ->whereIn('type', array_merge([CategoryTypeEnum::Region->value], CategoryTypeEnum::getTypeLottery()));

        if ($code) {
            return $query->where('code', $code)->firstOrFail();
        }

        return $query->get();
    }
    public function headTailStatisticCategory(?string $code): Category|Collection
    {
        $query = $this->applierList::apply($this->newQuery())
            ->whereNotIn('code', ['VIETLOTT'])
            ->whereIn('type', [CategoryTypeEnum::Region->value, CategoryTypeEnum::RegionVsLottery->value]);

        if ($code) {
            return $query->where('code', $code)->firstOrFail();
        }

        return $query->get();
    }
    public function getChildren(Category $category, CategoryTypeEnum $enum): Collection
    {
        return FeSelectListCategory::apply($category->children())
            ->where('type', $enum)
            ->with([
                'url' => fn($q) => FeSelectListUrl::apply($q)
            ])
            ->get();
    }

    public function getLotteryHasDetail(string $code): Category
    {
        return $this->applierDetail::apply($this->newQuery())
            ->where('code', $code)
            ->where('show_detail', true)
            ->with([
                'url' => fn($q) => FeSelectDetailUrl::apply($q),
//                'parent' => fn($q) => $this->applierList::apply($q)
            ])
            ->firstOrFail();
    }

    public function searchCategoryCrawl(string $name): ?int
    {
        return $this->newQuery()
            ->select('id')
            ->whereRaw('INSTR(keyword, ?) > 0', [$name])
            ->first()?->id;
    }
}
