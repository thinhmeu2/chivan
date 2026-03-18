<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Http\Requests\Be\RoleRequest;
use App\Models\BaseModel;
use App\Models\Role;

class RoleService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Role::class));
    }

    protected function handleBeforeDelete(BaseModel $model)
    {
        // TODO: Implement handleBeforeDelete() method.
    }

    protected function handleAfterCreate(BaseModel $model, BaseBeRequest $data)
    {
        $model->permissions()->sync($data->validatedForPermission());
    }

    protected function handleBeforeUpdate(BaseModel $model, BaseBeRequest|RoleRequest $data)
    {
        $model->permissions()->sync($data->validatedForPermission());
    }
}
