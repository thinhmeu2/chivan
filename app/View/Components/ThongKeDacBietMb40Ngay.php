<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ThongKeDacBietMb40Ngay extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'thong-ke-dac-biet-mb40-ngay';
    }

    protected function logicGetData(): array
    {
        $url = 'https://lodephomnayvip.org/';
        $crawler = new Crawler(Http::get($url)->body());
        $html = $crawler->filter('.widget-content')->html();
        // replace class
        $html = str_replace('tk-nhanh-item-header', 'bg-purple p-2 text-white', $html);
        $html = str_replace('table col100', 'border-red table-layout-fixed', $html);
        $html = str_replace(' class="tk-nhanh-number"', '', $html);
        $html = str_replace('class="bold"', 'class="fw-7 text-red"', $html);

        return [
            'html' => $html,
        ];
    }
}
