<?php

namespace App\DTO\Result;

use App\DTO\BaseDto;
use App\DTO\Category\CategoryListDto;
use App\Models\BaseModel;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ResultListDto extends BaseDto
{
    public array $categoryDow;
    public array $categoryRegion;
    public array $results;
    public Carbon $draw_date;

    protected function __construct(Result $item)
    {
        $this->categoryDow  = CategoryListDto::from($item->categoryDow);
        $this->categoryRegion  = CategoryListDto::from($item->categoryRegion);
        $this->results   = [
            'category' => CategoryListDto::from($item->category),
            'results' => $item->getResults()
        ];
        $this->draw_date = Carbon::parse($item->draw_date);
    }

    public static function from(BaseModel|LengthAwarePaginator|Collection|null $model): array
    {
        if (! $model instanceof \Illuminate\Database\Eloquent\Collection) {
            throw new \LogicException("Model cần là Illuminate\Database\Eloquent\Collection");
        }

        // Nếu là grouped theo draw_date
        if ($model->first() instanceof Collection) {
            return $model
                ->map(function (Collection $group) {
                    /** @var Result $first */
                    $first = $group->first();

                    return [
                        'categoryDow'    => CategoryListDto::from($first->categoryDow),
                        'categoryRegion' => CategoryListDto::from($first->categoryRegion),
                        'draw_date'      => Carbon::parse($first->draw_date),
                        'results'        => $group->map(function (Result $item) {
                            return [
                                'category' => CategoryListDto::from($item->category),
                                'results'  => $item->getResults(),
                            ];
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();
        }

        return parent::from($model);
    }
}
