<?php

namespace App\Console\Commands\DB;

use App\Console\Commands\BaseCommand;
use App\Enums\CategoryTypeEnum;
use App\Enums\ResultPrizeCodeEnum;
use Illuminate\Support\Facades\DB;

class Result extends BaseCommand
{
    protected $signature = 'db:results';
    protected $description = 'Convert result fast, not torture MySQL';

    protected $db_api;
    protected array $categories;

    protected function executeCommand(): void
    {
        $this->db_api = DB::connection('mysql_api');
        $this->db_api->statement("delete from st_result where id in(32704, 32709)");
//        $this->db_api->statement("update st_result set category_id = 10 where id in(32709)");

        /**
         * Lấy category map theo code
         * Chỉ lấy loại Lottery & RegionVsLottery
         */
        $this->categories = DB::table('categories')
            ->whereIn('type', CategoryTypeEnum::getTypeLottery())
            ->pluck('id', 'code')
            ->toArray();

        $limit    = 1000;
        $lastTime = '0000-00-00';
        $lastId   = 0;

        do {
            $rows = $this->db_api->select(
                'select id, category_id, data_result, displayed_time
                 from st_result
                 where (
                        (displayed_time > ?)
                     or (displayed_time = ? and id > ?)
                 )
                 and data_result REGEXP "\"[0-9]+\""
                 and category_id > 0
                 order by displayed_time asc, id asc
                 limit ?',
                [$lastTime, $lastTime, $lastId, $limit]
            );

            if (! $rows) break;

            $resultInserts = [];
            $detailInserts = [];

            foreach ($rows as $row) {
                $row = (array) $row;

                if (! $row['category_id']) dd(1, $row);

                $code = $this->mapApiCategoryToCode($row['category_id']);
                if (! $code) dd(2, $row);

                $categoryId = $this->categories[$code] ?? null;
                if (! $categoryId) dd(3, $row);

                $resultInserts[] = [
                    'category_id' => $categoryId,
                    'draw_date'   => $row['displayed_time'],
                ];
            }

            if (! $resultInserts) {
                $lastTime = end($rows)->displayed_time;
                dd(4, $row);
            }

            DB::table('results')->insert($resultInserts);

            $firstId   = DB::getPdo()->lastInsertId();
            $resultIds = range($firstId, $firstId + count($resultInserts) - 1);

            $resultCursor = 0;

            foreach ($rows as $row) {
                $row = (array) $row;

                if (! $row['category_id']) dd(5, $row);

                $code = $this->mapApiCategoryToCode($row['category_id']);
                if (! $code) dd(6, $row);

                if (! isset($this->categories[$code])) dd(7, $row);

                $resultId = $resultIds[$resultCursor];
                $resultCursor++;

                $results = json_decode($row['data_result'], true);
                if (! is_array($results)) dd(8, $row);

                if ($code === 'XSMB') {
                    $this->buildXsmbDetails($detailInserts, $results, $resultId);
                } else {
                    $this->buildNormalDetails($detailInserts, $results, $resultId);
                }

                $lastTime = $row['displayed_time'];
                $lastId   = $row['id'];
            }

            foreach (array_chunk($detailInserts, 2000) as $chunk) {
                DB::table('result_details')->insert($chunk);
            }
        } while (true);
    }

    /**
     * Map category_id từ DB api sang code
     */
    private function mapApiCategoryToCode(int $apiCategoryId): ?string
    {
        static $map = null;

        if ($map === null) {
            $rows = $this->db_api->select(
                'select id, code from st_category where `table` = "result"'
            );

            $map = array_column($rows, 'code', 'id');
        }

        return $map[$apiCategoryId] ?? null;
    }

    /**
     * XSMB có mã đặc biệt + g0..g7
     */
    private function buildXsmbDetails(array &$detailInserts, array $results, int $resultId): void
    {
        // mã đặc biệt
        $specialCodes = array_filter(
            $results[0] ?? [],
            fn ($i) => preg_match('/\d\w\w/', $i)
        );

        foreach ($specialCodes as $k => $number) {
            $detailInserts[] = [
                'result_id'  => $resultId,
                'prize_code' => ResultPrizeCodeEnum::Madb->value,
                'position'   => $k,
                'number'     => $this->normalizeNumber($number),
            ];
        }
        unset($results[0]);

        foreach ($results as $k => $group) {
            $enum = ResultPrizeCodeEnum::from('g' . ($k - 1));

            foreach ($group as $pos => $number) {
                $detailInserts[] = [
                    'result_id'  => $resultId,
                    'prize_code' => $enum->value,
                    'position'   => $pos,
                    'number'     => $this->normalizeNumber($number),
                ];
            }
        }
    }

    /**
     * Các tỉnh thường: gN đảo ngược
     */
    private function buildNormalDetails(array &$detailInserts, array $results, int $resultId): void
    {
        $maxPrizeIndex = count($results) - 1;

        foreach ($results as $k => $group) {
            $enum = ResultPrizeCodeEnum::from(
                'g' . ($maxPrizeIndex - $k)
            );

            foreach ($group as $pos => $number) {
                $detailInserts[] = [
                    'result_id'  => $resultId,
                    'prize_code' => $enum->value,
                    'position'   => $pos,
                    'number'     => $this->normalizeNumber($number),
                ];
            }
        }
    }

    private function normalizeNumber(mixed $number): ?string
    {
        if ($number === null || $number === '') {
            return null;
        }

        return (string) $number;
    }
}
