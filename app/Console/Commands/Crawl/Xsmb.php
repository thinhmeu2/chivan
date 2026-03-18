<?php

namespace App\Console\Commands\Crawl;

use App\Console\Commands\BaseCommand;
use App\Enums\CategoryTypeEnum;
use App\Enums\ResultPrizeCodeEnum;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class Xsmb extends BaseCommand
{
    use CommonCrawl;

    protected $signature = 'crawler:xsmb {--date=}';
    protected $description = 'cào ixoso XSMB';

    private int $category_id = 0;

    protected function executeCommand(): void
    {
        $category_id = Category::query()
            ->where('code', 'XSMB')
            ->where('type', CategoryTypeEnum::RegionVsLottery)
            ->firstOrFail()
            ->id;

        $date = Carbon::createFromFormat('Y-m-d', $this->option('date'));
        if (! $date)
            throw new \LogicException("Not valid format date: $date. Valid format: Y-m-d");

        $url = 'https://ixoso.com/xsmb-ngay-'.$date->format('d-m-Y').'.html';

        $crawler = new Crawler(Http::get($url)->body());
        $tableResult = $crawler->filter('.table-result');

        $maDb = $tableResult->filter('.number-prize > *')->each(function (Crawler $node, $i) {
            return $node->text();
        });
        // lấy suffix từ phần tử cuối
        $suffix = preg_replace('/^\d+/', '', end($maDb));

        // gắn suffix vào tất cả phần tử TRỪ phần tử cuối
        $lastIndex = count($maDb) - 1;

        foreach ($maDb as $i => &$v) {
            if ($i === $lastIndex) {
                continue;
            }
            $v .= $suffix;
        }
        unset($v);

        $details = $tableResult
            ->filter('tr:nth-child(n+2) td:nth-child(2)')
            ->each(function (Crawler $td, int $nth) {

                $prizeCodeEnum = [
                    ResultPrizeCodeEnum::G0,
                    ResultPrizeCodeEnum::G1,
                    ResultPrizeCodeEnum::G2,
                    ResultPrizeCodeEnum::G3,
                    ResultPrizeCodeEnum::G4,
                    ResultPrizeCodeEnum::G5,
                    ResultPrizeCodeEnum::G6,
                    ResultPrizeCodeEnum::G7
                ][$nth];

                return $td->filter('span')->each(function (Crawler $number, int $position) use ($prizeCodeEnum) {
                    return [
                        'prize_code' => $prizeCodeEnum->value,
                        'position' => $position,
                        'number' => $this->onlyNumber($number->text()),
                    ];
                });

            });

        $details = array_merge(...$details);
        // thêm result_details từ maDb
        $details = array_merge(
            $details,
            array_map(
                fn ($v, $i) => [
                    'prize_code' => ResultPrizeCodeEnum::Madb->value,
                    'position'   => $i,
                    'number'     => $v,
                ],
                $maDb,
                array_keys($maDb)
            )
        );;
        $result = [
            'result' => [
                'category_id' => $category_id,
                'draw_date' => $date->format('Y-m-d'),
            ],
            'details' => $details
        ];
        $this->saveResult($result);
    }
}
