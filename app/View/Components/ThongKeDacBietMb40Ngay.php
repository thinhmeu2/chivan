<?php

namespace App\View\Components;

class ThongKeDacBietMb40Ngay extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'thong-ke-dac-biet-mb40-ngay';
    }

    protected function logicGetData(): array
    {
        $crawler = $this->crawler('https://lodephomnayvip.org/');
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
