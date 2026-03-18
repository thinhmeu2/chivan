<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Http\Requests\Be\PostRequest;
use App\Models\BaseModel;
use App\Models\Post;
use App\Models\Revision;

class PostService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Post::class));
    }

    protected function handleBeforeDelete(BaseModel|Post $model)
    {
        $model->revisions()->delete();
        resolve(RevisionService::class)->delete(new Revision());
    }

    protected function handleAfterCreate(BaseModel|Post $model, BaseBeRequest|PostRequest $data)
    {

    }

    protected function handleBeforeUpdate(BaseModel|Post $model, BaseBeRequest|PostRequest $data)
    {

    }
}
