<?php

namespace App\Console\Commands\Crawl;

use App\Console\Commands\BaseCommand;
use App\Enums\CategoryTypeEnum;
use App\Enums\ResultPrizeCodeEnum;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class Max3dIxoso extends BaseCommand
{
    use CommonCrawl;

    protected $signature = 'crawler:max3d {--latest : Chỉ crawl kỳ quay mới nhất}';
    protected $description = 'cào ixoso max3d';

    private int $category_id = 0;

    protected function executeCommand(): void
    {
        $this->category_id = Category::query()
            ->where('code', 'MAX3D')
            ->where('type', CategoryTypeEnum::Lottery)
            ->firstOrFail()
            ->id;

        $isLatest = $this->option('latest');

        $sources = $isLatest
            ? ['https://ixoso.com/xo-so-tu-chon-max3d.html']
            : [
                'https://ixoso.com/kq-xo-so-max3d-thu-2.html',
                'https://ixoso.com/kq-xo-so-max3d-thu-4.html',
                'https://ixoso.com/kq-xo-so-max3d-thu-6.html',
            ];

        $results = [];

        foreach ($sources as $url) {
            $crawler = new Crawler(Http::get($url)->body());

            $items = $crawler
                ->filter('.content-left section.section')
                ->reduce(function (Crawler $section) {
                    return $section->filter('.number-prize')->count() > 0;
                })
                ->each(function (Crawler $section) {
                    return $this->detectSection($section);
                });

            $results = array_merge($results, array_filter($items));
        }

        // sort theo draw_date tăng dần
        usort($results, fn ($a, $b) =>
        strcmp($a['result']['draw_date'], $b['result']['draw_date'])
        );

        foreach ($results as $data) {
            $this->saveResult($data);
        }
    }

    private function detectSection(Crawler $section): array
    {
        $headerText = $section->filter('.site-link')->text();

        $drawDate = $this->getDrawDate($headerText);
        preg_match('#\#(\d+)#', $headerText, $period);

        $details = [];
        // Drawing period
        $details[] = [
            'prize_code' => ResultPrizeCodeEnum::DrawingPeriod->value,
            'position'   => 0,
            'number'     => $period[1],
        ];

//        number
        $section->filter('.table-result td:nth-child(2)')
            ->each(function (Crawler $td, $k) use (&$details){
                $prizeCode = match ($k){
                    0 => ResultPrizeCodeEnum::G0,
                    1 => ResultPrizeCodeEnum::G1,
                    2 => ResultPrizeCodeEnum::G2,
                    3 => ResultPrizeCodeEnum::G3,
                };
                $td->filter('span')->each(function (Crawler $span, $position) use (&$details, $prizeCode){
                    $details[] = [
                        'prize_code' => $prizeCode->value,
                        'position'   => $position,
                        'number'     => $span->text(),
                    ];
                });
            });

        $section->filter('table:not(.table-result) td:nth-child(3)')->each(function (Crawler $td, $k) use (&$details){
            $key = match ($k){
                0 => ResultPrizeCodeEnum::CountWin1,
                1 => ResultPrizeCodeEnum::CountWin2,
                2 => ResultPrizeCodeEnum::CountWin3,
                3 => ResultPrizeCodeEnum::CountWin4,
                4 => ResultPrizeCodeEnum::CountWin5,
                5 => ResultPrizeCodeEnum::CountWin6,
                6 => ResultPrizeCodeEnum::CountWin7,
                7 => ResultPrizeCodeEnum::CountWin8,
                8 => ResultPrizeCodeEnum::CountWin9,
                9 => ResultPrizeCodeEnum::CountWin10,
                10 => ResultPrizeCodeEnum::CountWin11,
            };
            $details[] = [
                'prize_code' => $key->value,
                'position'   => 0,
                'number'     => $this->onlyNumber($td->text()),
            ];
        });

        return [
            'result' => [
                'category_id' => $this->category_id,
                'draw_date'   => $drawDate,
            ],
            'details' => $details,
        ];
    }
}
