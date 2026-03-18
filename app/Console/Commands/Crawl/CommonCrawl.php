<?php

namespace App\Console\Commands\Crawl;

use Carbon\Carbon;

trait CommonCrawl
{
    private function saveResult(array $data): void
    {
        \DB::transaction(function () use ($data) {

            $result = \App\Models\Result::firstOrCreate([
                'category_id' => $data['result']['category_id'],
                'draw_date'   => $data['result']['draw_date'],
            ]);

            $rows = [];

            foreach ($data['details'] as $detail) {
                $rows[] = [
                    'result_id'  => $result->id,
                    'prize_code' => $detail['prize_code'],
                    'position'   => $detail['position'],
                    'number'     => $detail['number'],
                ];
            }

            \DB::table('result_details')->upsert(
                $rows,
                ['result_id', 'prize_code', 'position'], // unique key
                ['number'] // column update
            );
        });
    }
    private function getDrawDate(string $string): string
    {
        preg_match('#\d{2}/\d{2}/\d{4}#', $string, $m);
        return Carbon::createFromFormat('d/m/Y', $m[0])->toDateString();
    }
    private function onlyNumber(string $value): string
    {
        return preg_replace('/\D+/', '', $value);
    }
}
