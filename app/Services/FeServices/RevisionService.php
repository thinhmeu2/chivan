<?php
namespace App\Services\FeServices;

use App\Enums\RevisionActionEnum;
use App\Models\Revision;
use Carbon\Carbon;

class RevisionService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Revision::class));
    }
    public function getCreatedAt(string $className, int $id): Carbon
    {
        $model = new $className;

        return $this->newQuery()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $id)
            ->whereIn('action', [
                RevisionActionEnum::Create,
                RevisionActionEnum::Update,
            ])
            ->orderByRaw("FIELD(action, ?, ?)", [
                RevisionActionEnum::Create,
                RevisionActionEnum::Update,
            ])
            ->orderBy('created_at', 'asc')
            ->first()
            ->created_at;
    }
}
