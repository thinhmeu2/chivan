<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Models\BaseModel;
use App\Models\Redirect;

class RedirectService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Redirect::class));
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
