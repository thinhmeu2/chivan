<?php
namespace App\Services\CrawlerServices;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseService
{
    public function __construct(public readonly BaseModel $model)
    {

    }
    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }
}
