<?php

namespace App\View\Components;

use App\Services\FeServices\SoiCauService;

class DanDe30Khung3 extends BaseEmbed
{
    use TraitSoicau;
    protected bool $saveDb = false;
    protected function getKey(): string
    {
        return 'dan-de30-khung3';
    }

    protected function logicGetData(): array
    {
        $rows = resolve(SoiCauService::class)->getByKey('dande30sokhung3');
        $tmp = [];
        foreach ($rows as $i){
            $winDay = $i->win_day;

            if (is_null($winDay))
                $td3 = $this->getTextWaiting();
            elseif (! $winDay)
                $td3 = $this->getTextMiss();
            else {
                $td3 = "Ăn đề <b class=text-red>" . array_key_first($i->number_win) . "</b> ngày $i->win_day";
            }
            $tmp[] = [
                $i->start_date->format('d').'-'.$i->getEndDate()->format('d/m/Y'),
                implode(' - ', $i->number),
                $td3
            ];
        }

        return [
            'rows' => $tmp,
        ];
    }
}
