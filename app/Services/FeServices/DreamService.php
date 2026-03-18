<?php
namespace App\Services\FeServices;

use App\Models\Dream;
use App\Pipes\FilterSearch;
use App\SelectColumns\Dream\FeSelectListDream;
use Illuminate\Pagination\LengthAwarePaginator;

class DreamService extends BaseService
{
    protected string $applierList = FeSelectListDream::class;

    public function __construct(){
        parent::__construct(resolve(Dream::class));
    }
    public function getList(int $limit, ?string $keyword): LengthAwarePaginator
    {
        return $this->applierList::apply($this->newQuery())
            ->pipe(new FilterSearch($keyword))
            ->paginate($limit);
    }
}
