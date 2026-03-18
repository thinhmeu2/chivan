<?php

namespace App\Enums;

enum CategoryTypeEnum: string
{
    case Region = 'region';
    case Dayofweek = 'dow';
    case Lottery = 'lottery';
    case RegionVsLottery = 'region-lottery';
    public function getTitle(): string
    {
        return match ($this){
            self::Region => "Miền",
            self::Dayofweek => "Thứ",
            self::Lottery => "Đài quay",
            self::RegionVsLottery => "Vừa miền vừa đài quay",
        };
    }
    static public function getTypeLottery(): array
    {
        return [CategoryTypeEnum::RegionVsLottery->value, CategoryTypeEnum::Lottery->value];
    }
}
