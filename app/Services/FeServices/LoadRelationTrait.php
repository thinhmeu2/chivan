<?php

namespace App\Services\FeServices;

use App\Enums\ModelEnum;
use App\Models\BaseModel;
use App\SelectColumns\DefaultApplySelectColumns;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

trait LoadRelationTrait
{
    /**
     * Cache model đã load theo request
     * key: class|id|mode
     */
    protected static array $requestLoaded = [];

    /**
     * Registry cache cho relation data
     * key: class|id|relation|mode
     */
    protected static array $relationRegistry = [];

    /* =========================
     * Request cache helpers
     * ========================= */

    protected function makeRequestKey(
        BaseModel $model,
        bool $loadList
    ): string {
        return get_class($model)
            . '|' . $model->getKey()
            . '|' . ($loadList ? 'list' : 'detail');
    }

    protected function isRequestCached(
        BaseModel $model,
        bool $loadList
    ): bool {
        return isset(
            static::$requestLoaded[
            $this->makeRequestKey($model, $loadList)
            ]
        );
    }

    protected function markRequestCached(
        BaseModel $model,
        bool $loadList
    ): void {
        static::$requestLoaded[
        $this->makeRequestKey($model, $loadList)
        ] = true;
    }

    protected function markRelatedCached(
        mixed $related,
        bool $loadList
    ): void {
        if ($related instanceof BaseModel) {
            $this->markRequestCached($related, $loadList);
            return;
        }

        if ($related instanceof Collection) {
            foreach ($related as $item) {
                if ($item instanceof BaseModel) {
                    $this->markRequestCached($item, $loadList);
                }
            }
        }
    }

    /* =========================
     * Relation registry helpers
     * ========================= */

    protected function makeRelationKey(
        BaseModel $model,
        string $relationChain,
        bool $loadList
    ): string {
        return get_class($model)
            . '|' . $model->getKey()
            . '|' . $relationChain
            . '|' . ($loadList ? 'list' : 'detail');
    }

    protected function restoreRelationFromRegistry(
        BaseModel $model,
        string $relationChain,
        mixed $data
    ): void {
        $parts = explode('.', $relationChain);
        $current = $model;

        foreach ($parts as $index => $relation) {
            if (!$current) {
                break;
            }

            // relation cuối
            if ($index === count($parts) - 1) {
                $current->setRelation($relation, $data);
                break;
            }

            // intermediate relation
            if (!$current->relationLoaded($relation)) {
                if (!method_exists($current, $relation)) {
                    break;
                }

                $related = $current->{$relation}()->getRelated();
                $current->setRelation($relation, $related);
            }

            $current = $current->getRelation($relation);
        }
    }

    /* =========================
     * Public API
     * ========================= */

    public function load(
        BaseModel|Collection $subject,
        string $relationChain,
        bool $loadList = true
    ): void {
        // 1. Chuẩn hóa về Collection
        $models = $subject instanceof BaseModel ? collect([$subject]) : $subject;
        if ($models->isEmpty()) return;

        // 2. Lọc danh sách cần load thực tế (chưa có trong registry)
        $modelsToLoad = new Collection();
        foreach ($models as $model) {
            $relationKey = $this->makeRelationKey($model, $relationChain, $loadList);

            if (isset(static::$relationRegistry[$relationKey])) {
                $this->restoreRelationFromRegistry($model, $relationChain, static::$relationRegistry[$relationKey]);
            } else {
                $modelsToLoad->push($model);
            }
        }

        if ($modelsToLoad->isEmpty()) return;

        // 3. Build constraints dựa trên model đầu tiên của nhóm cần load
        $constraints = $this->buildLoadConstraints(
            $modelsToLoad->first(),
            $relationChain,
            $loadList
        );

        if (empty($constraints)) return;

        // 4. Thực hiện loadMissing một lần cho cả collection (Tối ưu Eager Load)
        $modelsToLoad->loadMissing($constraints);

        // 5. Cập nhật Registry và Request Cache sau khi load
        foreach ($modelsToLoad as $model) {
            // Ghi registry
            $relationKey = $this->makeRelationKey($model, $relationChain, $loadList);
            static::$relationRegistry[$relationKey] = data_get($model, $relationChain);

            // Đánh dấu request cache cho từng path trong constraints
            foreach (array_keys($constraints) as $path) {
                $this->markRelatedCached(data_get($model, $path), $loadList);
            }
        }
    }

    /* =========================
     * Core logic
     * ========================= */

    protected function buildLoadConstraints(
        BaseModel $model,
        string $relationChain,
        bool $loadList
    ): array {
        $parts = explode('.', $relationChain);
        $currentModel = $model;
        $path = [];
        $constraints = [];

        foreach ($parts as $relationName) {

            if (!method_exists($currentModel, $relationName)) {
                break;
            }

            /** @var Relation $relation */
            $relation = $currentModel->{$relationName}();
            $relatedModel = $relation->getRelated();

            $path[] = $relationName;
            $relationPath = implode('.', $path);

            // đã load + cache đúng mode → skip
            if (
                $currentModel->relationLoaded($relationName) &&
                $this->isRelationCached(
                    $currentModel,
                    $relationName,
                    $loadList
                )
            ) {
                $loaded = $currentModel->getRelation($relationName);

                if (!$loaded) {
                    break;
                }

                $currentModel = $loaded instanceof BaseModel
                    ? $loaded
                    : $relatedModel;

                continue;
            }

            /** @var class-string<DefaultApplySelectColumns> $applier */
            $applier = ModelEnum::getApplier(
                $relatedModel,
                $loadList
            );

            $constraints[$relationPath] = static function ($q) use ($applier) {
                $applier::apply($q);
            };

            $currentModel = $relatedModel;
        }

        return $constraints;
    }

    protected function isRelationCached(
        BaseModel $parent,
        string $relationName,
        bool $loadList
    ): bool {
        if (!$parent->relationLoaded($relationName)) {
            return false;
        }

        $related = $parent->getRelation($relationName);

        if ($related instanceof BaseModel) {
            return $this->isRequestCached($related, $loadList);
        }

        if ($related instanceof Collection) {
            foreach ($related as $item) {
                if (
                    $item instanceof BaseModel &&
                    !$this->isRequestCached($item, $loadList)
                ) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
}
