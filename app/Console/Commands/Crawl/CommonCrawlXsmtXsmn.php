<?php

namespace App\Console\Commands\Crawl;
use App\Enums\ResultPrizeCodeEnum;
use App\Services\FeServices\CategoryService;
use App\Services\LogService;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

trait CommonCrawlXsmtXsmn
{
    use CommonCrawl;

    private CategoryService $categoryService;
    private array $prizes = [ResultPrizeCodeEnum::G8, ResultPrizeCodeEnum::G7, ResultPrizeCodeEnum::G6, ResultPrizeCodeEnum::G5, ResultPrizeCodeEnum::G4, ResultPrizeCodeEnum::G3, ResultPrizeCodeEnum::G2, ResultPrizeCodeEnum::G1, ResultPrizeCodeEnum::G0];
    private Crawler $table;
    private Carbon $date;

    public function detectTable(Crawler $table, Carbon $date)
    {
        $this->table = $table;
        $this->date = $date;
        $this->categoryService = resolve(CategoryService::class);


        $this->table->filter('th:nth-child(n+2)')->each(function (Crawler $th, int $posTh) {
            $cateName = trim($th->text());
            $category_id = $this->categoryService->searchCategoryCrawl($cateName);

            if (!$category_id) {
                LogService::crawl("Không tìm thấy category: $cateName");
                return;
            }

            $result = [
                'result' => [
                    'category_id' => $category_id,
                    'draw_date' => $this->date->format('Y-m-d'),
                ],
                'details' => [],
            ];

            // duyệt từng giải
            $this->table->filter('tbody tr')->each(function (Crawler $tr, int $posTr) use (&$result, $posTh) {

                $prizeEnum = $this->prizes[$posTr] ?? null;
                if (!$prizeEnum) {
                    return;
                }

                $td = $tr->filter('td:nth-child('.($posTh + 2).')');

                $td->filter('span')->each(function (Crawler $span, int $position) use (&$result, $prizeEnum) {

                    $result['details'][] = [
                        'prize_code' => $prizeEnum->value,
                        'position' => $position,
                        'number' => $this->onlyNumber($span->text()),
                    ];

                });

            });
            $this->saveResult($result);
        });
    }
}
