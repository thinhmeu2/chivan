<?php

namespace App\Services\FeServices;

use App\DTO\Category\CategoryListDto;
use App\DTO\Category\CategorySelectOptionDto;
use App\Enums\ResultPrizeCodeEnum;
use App\Models\Category;

class StatisticService
{
    public static array $months = [1,3,6];
    public static array $years = [1,3,6,12];


    public static function getFullDataFeLogan(string $code, int $month): array
    {
        if (! in_array($month, static::$months))
            throw new \RuntimeException("Khoảng thời gian không phù hợp");

        $code = strtoupper($code);
        $categories = resolve(CategoryService::class)->statisticCategories();
        $category = $categories->first(fn($i) => $i->code == $code);
        if (empty($category))
            throw new \RuntimeException("$code không có thống kê");

        $resultService = resolve(ResultService::class);

        $logan = $resultService->getLogan($category, $month);
        $loganCap = $resultService->getLoganCap($category, $month);
        $loganSpecialHead = $resultService->getLoganSpecial($category, 'head', $month);
        $loganSpecialTail = $resultService->getLoganSpecial($category, 'tail', $month);
        $loganSpecialSum = $resultService->getLoganSpecial($category, 'sum', $month);

        return [
            'logan' => $logan,
            'loganCap' => $loganCap,
            'loganSpecialHead' => $loganSpecialHead,
            'loganSpecialTail' => $loganSpecialTail,
            'loganSpecialSum' => $loganSpecialSum,
            'category' => CategorySelectOptionDto::from($category),
            'month' => $month,
        ];
    }
    public static function getFullDataFeDacbiet(string $code, int $month): array
    {
        if (! in_array($month, static::$months))
            throw new \RuntimeException("Khoảng thời gian không phù hợp");

        $code = strtoupper($code);
        $resultService = resolve(ResultService::class);
        $categories = resolve(CategoryService::class)->statisticCategories();
        $category = $categories->first(fn($i) => $i->code == $code);

        $statistic = $resultService->getDacbiet($category, $month);
        $countPeriod = count($statistic['list']);
        $statistic['list'] = $resultService->formatDataForCalendar($statistic['list']);

        return [
            'statistic' => $statistic,
            'categories' => CategorySelectOptionDto::from($categories),
            'category' => CategorySelectOptionDto::from($category),
            'months' => static::$months,
            'month' => $month,
            'countPeriod' => $countPeriod,
        ];
    }
    public static function getFullDataFeTansuat(string $code, int $month): array
    {
        $code = strtoupper($code);
        $resultService = resolve(ResultService::class);
        $categories = resolve(CategoryService::class)->statisticCategories();
        $category = $categories->first(fn($i) => $i->code == $code);

        if (! in_array($month, static::$years))
            throw new \RuntimeException("Khoảng thời gian không phù hợp");

        $statistic = $resultService->getTanSuat($category, $month);

        return [
            'statistic' => $statistic,
            'category' => CategorySelectOptionDto::from($category),
            'month' => $month,
        ];
    }

    public static function getFullDataFeLoto(string $code, int $month): array
    {
        $code = strtoupper($code);
        $resultService = resolve(ResultService::class);
        $categories = resolve(CategoryService::class)->statisticCategories();
        $category = $categories->first(fn($i) => $i->code == $code);

        if (! in_array($month, static::$years))
            throw new \RuntimeException("Khoảng thời gian không phù hợp");

        $statistic = $resultService->getLotoStatistic($category, $month);

        return [
            'statistic' => $statistic,
            'category' => CategorySelectOptionDto::from($category),
            'month' => $month,
        ];
    }

    public static function getFullDataFeLokep(string $code, int $month): array
    {
        $code = strtoupper($code);
        $resultService = resolve(ResultService::class);
        $categories = resolve(CategoryService::class)->statisticCategories();
        $category = $categories->first(fn($i) => $i->code == $code);

        if (! in_array($month, static::$months))
            throw new \RuntimeException("Khoảng thời gian không phù hợp");

        $statistic = $resultService->getLokepCap($category, $month);

        return [
            'statistic' => $statistic,
            'category' => CategorySelectOptionDto::from($category),
            'month' => $month,
        ];
    }

    public static function initDataHeadTail(Category $category, int $limit = 30): array
    {
        $collectionResultGetByCategory = resolve(ResultService::class)->getByCategory($category, $limit);

        $data = [];
        if ($category->code == 'XSMB')
            foreach ($collectionResultGetByCategory as $i){
                $data[] = [
                    'first' => $i->result_details->first(fn($i2) => $i2->prize_code == ResultPrizeCodeEnum::G0->value)?->number ?? '',
                    'last' => $i->result_details->filter(fn($i2) => $i2->prize_code == ResultPrizeCodeEnum::G7->value)->pluck('number')->toArray(),
                    'draw_date' => $i->getRawOriginal('draw_date')
                ];
            }
        else
            foreach ($collectionResultGetByCategory as $draw_date => $results){
                $list = [];
                foreach ($results as $i){
                    $list[] = [
                        'first' => $i->result_details->first(fn($i2) => $i2->prize_code == ResultPrizeCodeEnum::G0->value)?->number ?? '',
                        'last' => $i->result_details->first(fn($i2) => $i2->prize_code == ResultPrizeCodeEnum::G8->value)?->number ?? '',
                        'category' => CategoryListDto::from($i->category)
                    ];
                }

                $data[] = [
                    'list' => $list,
                    'draw_date' => $draw_date,
                    'category_dow' => CategoryListDto::from($i->categoryDow)
                ];
            }

        $grouped = [];

        foreach ($data as $item) {
            $time = strtotime($item['draw_date']);

            $year = date('o', $time);   // ISO week year
            $week = date('W', $time);   // week number

            $key = $year . '-W' . $week;

            $grouped[$key][] = $item;
        }

        return array_values($grouped);
    }
}
