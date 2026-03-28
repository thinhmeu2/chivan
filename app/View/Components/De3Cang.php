<?php

namespace App\View\Components;

use App\Services\FeServices\SoiCauService;

class De3Cang extends BaseEmbed
{
    use TraitSoicau;
    protected bool $saveDb = false;
    protected function getKey(): string
    {
        return 'de3cang';
    }

    protected function logicGetData(): array
    {
        $rows = resolve(SoiCauService::class)->getByKey('de3cang');
        $tmp = [];
        foreach ($rows as $i){
            $td1 = $i->start_date->format('d/m/Y');
            $td2 = implode(' ', $i->number);
            $winDay = $i->win_day;
            if (is_null($winDay))
                $td3 = $this->getTextWaiting();
            elseif (! $winDay)
                $td3 = $this->getTextMiss();
            else {
                $td3 = 'Trúng <b class=text-red>';
                foreach ($i->number_win as $number => $count){
                    $td3 .= " $number" . ($count>1 ? "x$count" : '');
                }
                $td3 .= '</b>';
            }
            $tmp[] = [$td1, $td2, $td3];
        }

        return [
            'rows' => $tmp,
        ];
    }
}
