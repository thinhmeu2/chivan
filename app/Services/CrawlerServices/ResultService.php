<?php

namespace App\Services\CrawlerServices;

use App\Enums\ResultPrizeCodeEnum;
use App\Models\Category;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResultService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Result::class));
    }
    public function makeResult(int $category_id, string|Carbon $draw_date): Result
    {
        $date = $draw_date instanceof Carbon
            ? $draw_date->toDateString()
            : Carbon::parse($draw_date)->toDateString();

        return Result::firstOrCreate(
            [
                'category_id' => $category_id,
                'draw_date'   => $date,
            ]
        );
    }
    public function saveDetails(Category $category, Result $result, array $details): bool
    {
        if (empty($details)) {
            return false;
        }

        // XSMB: Madb → G7
        // Khác: G8 → G0
        $prizeCodes = $category->code === 'XSMB'
            ? [
                ResultPrizeCodeEnum::Madb,
                ResultPrizeCodeEnum::G0,
                ResultPrizeCodeEnum::G1,
                ResultPrizeCodeEnum::G2,
                ResultPrizeCodeEnum::G3,
                ResultPrizeCodeEnum::G4,
                ResultPrizeCodeEnum::G5,
                ResultPrizeCodeEnum::G6,
                ResultPrizeCodeEnum::G7,
            ]
            : [
                ResultPrizeCodeEnum::G8,
                ResultPrizeCodeEnum::G7,
                ResultPrizeCodeEnum::G6,
                ResultPrizeCodeEnum::G5,
                ResultPrizeCodeEnum::G4,
                ResultPrizeCodeEnum::G3,
                ResultPrizeCodeEnum::G2,
                ResultPrizeCodeEnum::G1,
                ResultPrizeCodeEnum::G0,
            ];

        $rows = [];

        foreach ($details as $levelIndex => $numbers) {

            if (!isset($prizeCodes[$levelIndex])) {
                continue; // tránh out of range
            }

            $prizeCode = $prizeCodes[$levelIndex]->value;

            foreach ($numbers as $position => $number) {

                $rows[] = [
                    'result_id'  => $result->id,
                    'prize_code' => $prizeCode,
                    'position'   => $position,
                    'number'     => $number,
                ];
            }
        }

        if (empty($rows)) {
            return false;
        }

        return DB::transaction(function () use ($rows) {

            DB::table('result_details')->upsert(
                $rows,
                ['result_id', 'prize_code', 'position'], // primary key
                ['number'] // update nếu trùng
            );

            return true;
        });
    }
}
