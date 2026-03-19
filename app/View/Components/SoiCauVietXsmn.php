<?php

namespace App\View\Components;

use App\Helpers\CollectionHelper;
use App\Helpers\RandomHelper;
use App\Services\FeServices\CategoryService;

class SoiCauVietXsmn extends BaseEmbed
{
    protected function getKey(): string
    {
        return 'soi-cau-viet-xsmn';
    }

    protected function logicGetData(): array
    {
        $todayCategories = resolve(CategoryService::class)->todayCategories();
        $todayCategories = CollectionHelper::buildTree($todayCategories);

        foreach (['XSMN'] as $parentCode){
            $cate = $todayCategories->first(fn($i) => $i->code == $parentCode);
            foreach ($cate->children as $i){
                ${$parentCode}[] = [
                    'name' => $i->name,
                    'data' => [
                        RandomHelper::getRandomNumber(2,1),
                        RandomHelper::getRandomNumber(1,2),
                        RandomHelper::getRandomNumber(2,3),
                        RandomHelper::getRandomNumber(3,2),
                    ]
                ];
            }
        }
        return [
            'xsmn' => $XSMN
        ];
    }
}
