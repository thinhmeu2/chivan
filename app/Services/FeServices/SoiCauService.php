<?php
namespace App\Services\FeServices;

use App\Models\Soicau;
use App\SelectColumns\Soicau\FeSelectListResult;
use Illuminate\Database\Eloquent\Collection;

class SoiCauService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Soicau::class));
    }
    protected string $applierList = FeSelectListResult::class;
    public function getByKey(string $key, int $limit = 10): Collection
    {
        return $this->applierList::apply($this->newQuery())
            ->where('key', $key)
            ->orderByDesc('start_date')
            ->limit($limit)
            ->get();
    }
}
