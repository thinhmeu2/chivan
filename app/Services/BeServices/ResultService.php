<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Models\BaseModel;
use App\Models\Result;

class ResultService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Result::class));
    }

    protected function handleBeforeDelete(BaseModel $model)
    {
        // TODO: Implement handleBeforeDelete() method.
    }

    protected function handleAfterCreate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleAfterCreate() method.
    }

    protected function handleBeforeUpdate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleBeforeUpdate() method.
    }
}
