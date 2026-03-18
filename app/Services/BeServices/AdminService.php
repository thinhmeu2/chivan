<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\AdminRequest;
use App\Http\Requests\Be\BaseBeRequest;
use App\Models\Admin;
use App\Models\BaseModel;
use App\Models\Revision;

class AdminService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Admin::class));
    }

    protected function handleBeforeDelete(BaseModel|Admin $model)
    {
        $model->permissions()->detach();
        $model->revisions()->delete();
        resolve(RevisionService::class)->delete(new Revision());
    }

    protected function handleAfterCreate(BaseModel $model, AdminRequest|BaseBeRequest $data)
    {

    }

    protected function handleBeforeUpdate(BaseModel $model, AdminRequest|BaseBeRequest $data)
    {

    }
}
