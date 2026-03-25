<?php

namespace App\Services\CrawlerServices;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;

class AtrungroiService extends BaseService
{
    public function getResultXsmb(): array
    {
        return Cache::remember('getResultXsmb-atrungroi', 600, function (){
            $url = 'https://atrungroi.com/api/get/xoso/mien/more';
            $params = [
                'mien' => 11,
                'limit' => 8,
                'last_date' => date('Y-m-d'),
            ];
            $crawler = $this->crawler($url, 'post', $params);
            $data = $crawler->filter('.kqxs')->each(function (Crawler $node) {
                return [
                    'draw_date' => $this->getDrawDate($node),
                    'results' => $this->getLoto($node),
                ];
            });
            return $data;
        });
    }
    private function getDrawDate(Crawler $node): Carbon
    {
        $textA = $node->filter('.kqxs__tree a:last-child')->text();
        preg_match('#\d{1,2}/\d{1,2}/\d{4}#', $textA, $match);
        return Carbon::createFromFormat('j/n/Y', $match[0]);
    }
    private function getLoto(Crawler $node): array
    {
        $loto = $node->filter('.number')->each(function (Crawler $span) {
            return $span->text();
        });
        return $loto;
    }
}
