<?php

namespace App\View\Components;

use App\Helpers\CollectionHelper;
use App\Helpers\RandomHelper;
use App\Services\FeServices\CategoryService;

class SoiCauMobiXsmn extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-mobi-xsmn';
    }

    protected function logicGetData(): array
    {
        $todayCategories = resolve(CategoryService::class)->todayCategories();
        $todayCategories = CollectionHelper::buildTree($todayCategories);
        $todayCategories = $todayCategories->first(fn($i) => $i->code == 'XSMN')->children;

        $data = [];
        foreach ($todayCategories as $i) {
            $data[] = [
                $i->name,
                RandomHelper::getRandomNumber(2,1),
                RandomHelper::getRandomNumber(2,1),
                RandomHelper::getRandomNumber(2,2),
                RandomHelper::getRandomNumber(2,3),
                RandomHelper::getRandomNumber(2,5),
                RandomHelper::getRandomNumber(2,5),
            ];
        }

        return [
            'data' => $data,
        ];
    }
}
