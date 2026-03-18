<?php
namespace App\Services\BeServices;

use App\Enums\RevisionActionEnum;
use App\Http\Requests\Be\BaseBeRequest;
use App\Models\BaseModel;
use App\Models\RevisionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    public array $selectList = ['*'];
    public array $selectDetail = ['*'];
    public function __construct(protected BaseModel $model)
    {

    }


    public function getList(null|Builder|Relation $builder = null): Builder|Relation
    {
        if (! $builder)
            return $this->model->newQuery()->select($this->selectList)->orderByDesc('id');
        return $builder->select($this->selectList);
    }
    public function getDetail(null|Builder|Relation $builder = null): Builder|Relation
    {
        if (! $builder)
            return $this->model->newQuery()->select($this->selectDetail);
        return $builder->select($this->selectDetail);
    }
    public function builder(null|Builder|Relation $builder = null): Builder|Relation
    {
        if (! $builder)
            return $this->model->newQuery()->select($this->selectList)->orderByDesc('id');
        return $builder->select($this->selectList);
    }
    public function findOrFail(int $id): BaseModel
    {
        return $this->builder()->findOrFail($id);
    }
    public function findOrNew(int $id): BaseModel
    {
        return $this->builder()->findOrNew($id);
    }

    protected function buildTreeFromCollection(
        Collection $collection,
        string $fieldParentId = 'parent_id',
        bool $onlyActive = false,
        int $rootParentId = 0
    ): Collection {
        // Nếu không lấy soft deleted thì filter bỏ
        if ($onlyActive) {
            $collection = $collection->filter(fn ($item) => $item->is_active);
        }

        $grouped = $collection
            ->map(function ($item) use ($fieldParentId, $rootParentId) {
                // Chuẩn hoá: null => rootParentId (0)
                if ($item->{$fieldParentId} === null) {
                    $item->{$fieldParentId} = $rootParentId;
                }

                return $item;
            })
            ->groupBy($fieldParentId);

        $build = function ($parentId) use (&$build, $grouped) {
            return ($grouped[$parentId] ?? collect())->map(function ($item) use ($build) {
                $children = $build($item->id);
                $item->setRelation('children', $children); // để giống Eloquent relation
                return $item;
            });
        };

        return $build($rootParentId);
    }
    protected function saveTrees(array $data, \Illuminate\Database\Eloquent\Collection $collection, ?int $parent_id = null): void
    {
        foreach (array_reverse($data) as $order => $i){
            if (! empty($i['children'])){
                $this->saveTrees($i['children'], $collection, $i['id']);
            }
            $model = $collection->first(fn($i2) => $i2->id == $i['id']);
            $model->parent_id = $parent_id;
            $model->order = $order;
            $model->save();
        }
    }

    public function resetOrderColumn(): void
    {
        $table = $this->model->getTable();

        DB::transaction(function () use ($table) {
            DB::statement('SET @row = 0;');
            DB::statement("UPDATE `{$table}` SET `order` = (@row := @row + 1) ORDER BY `order` ASC, `id` ASC;");
        });
    }

    protected abstract function handleBeforeDelete(BaseModel $model);
    public function delete(BaseModel $model, bool $saveRevision = true): void
    {
        $this->handleBeforeDelete($model);

        if ($saveRevision && in_array(RevisionTrait::class, class_uses($model)))
            resolve(RevisionService::class)->saveRevision($model, RevisionActionEnum::Delete);
        $model->delete();
    }

    protected abstract function handleAfterCreate(BaseModel $model, BaseBeRequest $data);
    public function create(BaseBeRequest|array $data, bool $saveRevision = true): BaseModel
    {
        $model = $this->model->newQuery()->create(is_array($data) ? $data : $data->validated());
        if ($data instanceof BaseBeRequest) {
            $this->handleAfterCreate($model, $data);
        }
        if ($saveRevision) {
            resolve(RevisionService::class)->saveRevision($model, RevisionActionEnum::Create);
        }
        return $model;
    }

    protected abstract function handleBeforeUpdate(BaseModel $model, BaseBeRequest $data);
    public function update(BaseModel $model, BaseBeRequest|array $data, bool $saveRevision = true): void
    {
        if (! $model->exists)
            throw new ModelNotFoundException();
        if ($data instanceof BaseBeRequest)
            $this->handleBeforeUpdate($model, $data);
        $model->fill(is_array($data) ? $data : $data->validated());
        if ($saveRevision)
            resolve(RevisionService::class)->saveRevision($model, RevisionActionEnum::Update);
        $model->save();
    }
}
