<?php

namespace App\View\Components;

use App\Helpers\DateHelper;

class KqxsXsmbHomQua extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'kqxs-xsmb-hom-qua';
    }

    protected function logicGetData(): array
    {
        $yesterday = DateHelper::yesterday();
        $jn = $yesterday->format('j-n');
        $jnY = $yesterday->format('j-n-Y');
        $crawler = $this->crawler("https://atrungroi.com/xsmb-$jn-ket-qua-xo-so-mien-bac-ngay-$jnY.html");
        $html = $crawler->filter('.kq-table.xsmb')->outerHtml();
        $html = str_replace('table align-middle kq-table xsmb js-kq-table', 'table-kqxs table-kqxs-xsmb text-center fw-7', $html);
        $html = str_replace('fs-6', 'fs-12', $html);
        $html = str_replace(' class="d-block d-sm-inline-block fw-normal"', '', $html);
        $html = preg_replace('# data-page-id\S+#', '', $html);
        $html = preg_replace('# data-id-giai\S+#', '', $html);
        $html = preg_replace('# data-num[^\s>]+#', '', $html);
        $html = preg_replace('#class="[^"]*? number"#', 'class="number"', $html);

        return [
            'date' => $yesterday,
            'html' => $html
        ];
    }
}
