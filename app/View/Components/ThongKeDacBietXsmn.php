<?php

namespace App\View\Components;

use Symfony\Component\DomCrawler\Crawler;

class ThongKeDacBietXsmn extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'thong-ke-dac-biet-xsmn';
    }

    protected function logicGetData(): array
    {
        $crawler = $this->crawler('https://atrungroi.com/thong-ke-xsmn.html');
        $result = $crawler->filter('h2')->each(function (Crawler $h2, int $i) {
            // HTML của h2
            $h2Html = $h2->outerHtml();
            $h2Html = str_replace('fs-5-5 m-0 p-2 px-3 bg-orange', 'p-2 bg-orange', $h2Html);
            $h2Html = str_replace('h2', 'div', $h2Html);

            // node kế tiếp (DOM)
            $nextNode = $h2->getNode(0)->nextSibling;

            // bỏ qua text node (xuống dòng, space)
            while ($nextNode && $nextNode->nodeType !== XML_ELEMENT_NODE) {
                $nextNode = $nextNode->nextSibling;
            }

            $nextHtml = null;

            if ($nextNode) {
                $nextHtml = $h2->getNode(0)->ownerDocument->saveHTML($nextNode);
                $nextHtml = str_replace('table-responsive', 'overflow-auto', $nextHtml);

                switch ($i){
                    case 0:
                        $nextHtml = str_replace('table class="table table-bordered small mb-0"', 'table class="table-layout-fixed"', $nextHtml);
                        $nextHtml = str_replace('fw-bold fs-6', 'fw-7', $nextHtml);
                        $nextHtml = str_replace('th class="fw-medium"', 'th', $nextHtml);
                        break;
                    case 1:
                        $nextHtml = str_replace('table class="table table-bordered small text-center mb-0"', 'table class="table-layout-fixed text-center"', $nextHtml);
                        $nextHtml = str_replace('th class="fw-medium"', 'th', $nextHtml);
                        $nextHtml = str_replace(' style="width: 15%; text-align: center;"', '', $nextHtml);
                        $nextHtml = str_replace('fw-bold fs-6', 'fw-7', $nextHtml);
                        $nextHtml = str_replace(' data-kyquay="30"', '', $nextHtml);
                        $nextHtml = str_replace(' data-mientinh="mb"', '', $nextHtml);
                        break;
                    default:
                        $nextHtml = str_replace('table class="table table-bordered text-center align-middle small mb-0"', 'table class="text-center"', $nextHtml);
                        $nextHtml = str_replace(' class="fw-medium"', '', $nextHtml);
                        $nextHtml = str_replace(' style="width: 20%; vertical-align: middle;"', '', $nextHtml);
                        $nextHtml = str_replace(' js-tk-number', '', $nextHtml);
                        $nextHtml = str_replace(' bg-success', '', $nextHtml);
                        $nextHtml = str_replace(' role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"', '', $nextHtml);
                        $nextHtml = str_replace(' style="width: 45%;"', '', $nextHtml);
                        $nextHtml = str_replace(' style="height: 5px;"', '', $nextHtml);
                        $nextHtml = str_replace(' text-align: center;', '', $nextHtml);
                        $nextHtml = str_replace(' vertical-align: middle;', '', $nextHtml);
                        $nextHtml = str_replace('fw-bold fs-6', 'fw-7', $nextHtml);
                        $nextHtml = str_replace(' data-kyquay="30"', '', $nextHtml);
                        $nextHtml = str_replace(' data-mientinh="mb"', '', $nextHtml);
                }
            }

            return $h2Html.$nextHtml;
        });

        return [
            'html' => implode($result),
        ];
    }
}
