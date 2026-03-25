<?php

namespace App\View\Components;

use App\Helpers\DateHelper;
use App\Helpers\RandomHelper;

class SoiCauWapXsmn extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-wap-xsmn';
    }

    protected function logicGetData(): array
    {
        $todayCategories = $this->getTodayCategories('XSMN');
        $data = [];
        foreach ($todayCategories as $name){
            $td1 = RandomHelper::getRandomNumber(1,2);

            $td3 = RandomHelper::getRandomNumber(2,8);
            $tmpTd3 = '';
            for ($i = 0; $i < count($td3); $i += 2) {
                $tmpTd3 .= '(' . $td3[$i] . ' - ' . $td3[$i + 1] . ') ';
            }

            $data[] = [
                'name' => $name,
                'data' => [
                    "Đầu $td1[0], Đuôi $td1[1]",
                    implode(' - ', RandomHelper::getRandomLokep(2)),
                    $tmpTd3,
                    implode(' - ', RandomHelper::getRandomNumber(2,4)),
                    implode(' - ', RandomHelper::getRandomNumber(2,4)),
                ]
            ];
        }

        return [
            'data' => $data,
        ];
    }
}
