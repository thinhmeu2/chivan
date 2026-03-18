<?php

namespace App\Console\Commands\Crawl;

use App\Console\Commands\BaseCommand;
use App\Enums\CategoryTypeEnum;
use App\Enums\ResultPrizeCodeEnum;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class MegaIxoso extends BaseCommand
{
    use CommonCrawl;

    protected $signature = 'crawler:mega {--latest : Chỉ crawl kỳ quay mới nhất}';
    protected $description = 'cào ixoso mega';

    private int $category_id = 0;

    protected function executeCommand(): void
    {
        $this->category_id = Category::query()
            ->where('code', 'MEGA')
            ->where('type', CategoryTypeEnum::Lottery)
            ->firstOrFail()
            ->id;

        $isLatest = $this->option('latest');

        $sources = $isLatest
            ? ['https://ixoso.com/xo-so-tu-chon-mega-645.html']
            : [
                'https://ixoso.com/kq-xo-so-mega645-thu-4.html',
                'https://ixoso.com/kq-xo-so-mega645-thu-6.html',
                'https://ixoso.com/kq-xo-so-mega645-cn-chu-nhat.html',
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
            };
            $details[] = [
                'prize_code' => $winningCode->value,
                'position'   => 0,
                'number'     => $this->onlyNumber($td->text()),
            ];
        });
        $td = $section->filter('td:nth-child(4)')->first();

        if ($td->count()) {
            $details[] = [
                'prize_code' => ResultPrizeCodeEnum::MoneyWin1,
                'position'   => 0,
                'number'     => $this->onlyNumber($td->text()),
            ];
        }

        return [
            'result' => [
                'category_id' => $this->category_id,
                'draw_date'   => $drawDate,
            ],
            'details' => $details,
        ];
    }
}
