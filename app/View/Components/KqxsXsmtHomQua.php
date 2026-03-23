<?php

namespace App\View\Components;

use App\Helpers\DateHelper;

class KqxsXsmtHomQua extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'kqxs-xsmt-hom-qua';
    }

    protected function logicGetData(): array
    {
        $yesterday = DateHelper::yesterday();
        $jn = $yesterday->format('j-n');
        $jnY = $yesterday->format('j-n-Y');

        $crawler = $this->crawler("https://atrungroi.com/xsmt-$jn-ket-qua-xo-so-mien-trung-ngay-$jnY.html");

        // lấy table
        $tableNode = $crawler->filter('.kq-table')->first();

        // xóa từ tr thứ 11 trở đi
        $tableNode->filter('tr')->each(function ($node, $i) {
            if ($i >= 10) {
                foreach ($node as $domNode) {
                    $domNode->parentNode?->removeChild($domNode);
                }
            }
        });

        // lấy lại html sau khi đã xóa
        $html = $tableNode->outerHtml();

        // giữ nguyên mớ replace của mày
        $html = str_replace('table align-middle kq-table Sunday xsmt js-kq-table', 'table-kqxs table-kqxs-multi text-center fw-7', $html);
        $html = str_replace('fs-6', 'fs-12', $html);
        $html = str_replace(' class="d-block d-sm-inline-block fw-normal"', '', $html);
        $html = preg_replace('# data-page-id\S+#', '', $html);
        $html = preg_replace('# data-id-giai\S+#', '', $html);
        $html = preg_replace('# data-num[^\s>]+#', '', $html);
        $html = preg_replace('#<a.*?>(.*?)</a>#', '$1', $html);

        return [
            'date' => $yesterday,
            'html' => $html
        ];
    }
}
