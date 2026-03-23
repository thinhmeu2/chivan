<?php

namespace App\View\Components;

class SoiCauXsmn extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-xsmn';
    }

    protected function logicGetData(): array
    {
        $todayCategories = $this->getTodayCategories('XSMN');
        return [
            'todayCategories' => $todayCategories,
        ];
    }
}
