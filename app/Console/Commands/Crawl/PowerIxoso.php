<?php

namespace App\Console\Commands\Crawl;

use App\Console\Commands\BaseCommand;
use App\Enums\CategoryTypeEnum;
use App\Enums\ResultPrizeCodeEnum;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class PowerIxoso extends BaseCommand
{
    use CommonCrawl;

    protected $signature = 'crawler:power {--latest : Chỉ crawl kỳ quay mới nhất}';
    protected $description = 'cào ixoso power';

    private int $category_id = 0;

    protected function executeCommand(): void
    {
        $this->category_id = Category::query()
            ->where('code', 'POWER')
            ->where('type', CategoryTypeEnum::Lottery)
            ->firstOrFail()
            ->id;

        $isLatest = $this->option('latest');

        $sources = $isLatest
            ? ['https://ixoso.com/xo-so-tu-chon-power-655.html']
            : [
                'https://ixoso.com/kq-xo-so-power655-thu-3.html',
                'https://ixoso.com/kq-xo-so-power655-thu-5.html',
                'https://ixoso.com/kq-xo-so-power655-thu-7.html',
            ];

        $results = [];

        foreach ($sources as $url) {
            $crawler = new Crawler(Http::get($url)->body());

            $items = $crawler
                ->filter('.content-left section.section')
                ->reduce(function (Crawler $section) {
                    return $section->filter('.btn-results')->count() > 0;
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
        $drawDate = $this->getDrawDate($section->filter('header.section-header h2')->text());

        $details = [];
        // Drawing period
        $details[] = [
            'prize_code' => ResultPrizeCodeEnum::DrawingPeriod->value,
            'position'   => 0,
            'number'     => $this->onlyNumber($section->filter('.jackpot-item strong')->text()),
        ];

        // G0 numbers
        $numbers = $section->filter('.btn-results')
            ->each(fn (Crawler $n) => $this->onlyNumber($n->text()));

        foreach ($numbers as $i => $num) {
            $details[] = [
                'prize_code' => ResultPrizeCodeEnum::G0->value,
                'position'   => $i,
                'number'     => $num,
            ];
        }

        // Prize table
        $section->filter('td:nth-child(3)')->each(function (Crawler $td, $k) use (&$details) {
            $winningCode = match ($k) {
                0 => ResultPrizeCodeEnum::CountWin1,
                1 => ResultPrizeCodeEnum::CountWin2,
                2 => ResultPrizeCodeEnum::CountWin3,
                3 => ResultPrizeCodeEnum::CountWin4,
                4 => ResultPrizeCodeEnum::CountWin5,
            };
            $details[] = [
                'prize_code' => $winningCode->value,
                'position'   => 0,
                'number'     => $this->onlyNumber($td->text()),
            ];
        });
        $section->filter('td:nth-child(4)')
            ->slice(0, 2)
            ->each(function (Crawler $td, $k) use (&$details) {
            $valueCode = match ($k) {
                0 => ResultPrizeCodeEnum::MoneyWin1,
                1 => ResultPrizeCodeEnum::MoneyWin2,
            };

            $details[] = [
                'prize_code' => $valueCode->value,
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
