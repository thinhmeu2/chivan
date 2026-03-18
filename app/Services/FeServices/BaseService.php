<?php
namespace App\Services\FeServices;

use App\DTO\BaseDto;
use App\Helpers\BreadcrumbHelper;
use App\Models\BaseModel;
use App\Models\IsActiveTrait;
use App\SelectColumns\DefaultApplySelectColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseService
{
    use LoadRelationTrait;

    /** @var class-string<DefaultApplySelectColumns> */
    protected string $applierList;
    /** @var class-string<DefaultApplySelectColumns> */
    protected string $applierDetail;

    public function __construct(public readonly BaseModel $model)
    {

    }
    public function getByIds(array $ids, bool $sortById = false): Collection
    {
        $ids = array_filter($ids);
        $query = $this->newQuery()->whereIn('id', $ids);
        if ($sortById) {
            $query = $query->orderByRaw("FIELD(id, ".implode(',', $ids).")");
        }
        return $query->get();
    }
    public function newQuery(): Builder
    {
        $q = $this->model->newQuery();
        if (in_array(IsActiveTrait::class, class_uses($this->model)))
            return $q->IsActive();
        return $q;
    }
    public function getSitemapIndexData(int $perPage = 500): array
    {
        $totalItems = $this->newQuery()->whereHas('url', fn($q) => $q->where('is_index', true))
            ->count();

        $totalSitemaps = (int) ceil($totalItems / $perPage);

        $sitemapData = [];

        for ($page = 1; $page <= $totalSitemaps; $page++) {
            // dùng scope orderByUpdatedAt thay vì orderBy('updated_at')
            $latestRevision = $this->newQuery()->with(['WithCreatedAt', 'WithUpdatedAt'])
                ->orderByUpdatedAt('desc')
                ->offset(($page - 1) * $perPage)
                ->limit(1)
                ->first();

            $lastmod = $latestRevision?->updated_at ?? now();

            $sitemapData[] = [
                'loc' => $page,
                'lastmod' => $lastmod,
            ];
        }

        return $sitemapData;
    }

    public function buildBreadcrumbAndAppendRelation(BaseModel $model, string $dtoClassName): void
    {
        /** @var $dtoClassName BaseDto */
        $this->load($model, 'parent.url', true);
        if ($model->relationLoaded('parent') && $model->parent)
            $this->load($model->parent, 'parent.url', true);
        if (isset($model->parent->parent)){
            $i = $dtoClassName::from($model->parent->parent);
            BreadcrumbHelper::add($i['name'], $i['url']);
        }
        if (isset($model->parent)){
            $i = $dtoClassName::from($model->parent);
            BreadcrumbHelper::add($i['name'], $i['url']);
        }
        $i = $dtoClassName::from($model);
        BreadcrumbHelper::add($i['name'], $i['url']);
    }
}
