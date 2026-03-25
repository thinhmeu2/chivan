<?php
namespace App\Services\FeServices;

use App\Models\BaseModel;
use App\SelectColumns\DefaultApplySelectColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        /*if (in_array(IsActiveTrait::class, class_uses($this->model)))
            return $q->IsActive();*/
        return $q;
    }
    public function getList(): Relation|Builder
    {
        return $this->applierList::apply($this->newQuery());
    }
}
