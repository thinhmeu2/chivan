<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Models\Admin;
use App\Models\BaseModel;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new Permission());
    }

    public function getByAdmin(Admin $admin): Collection
    {
        return $admin->permissions()->get();
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
