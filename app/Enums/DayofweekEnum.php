<?php

namespace App\Enums;

enum DayofweekEnum: int
{
    case Mon = 1;
    case Tue = 2;
    case Wed = 3;
    case Thu = 4;
    case Fri = 5;
    case Sat = 6;
    case Sun = 7;

    public function getTitle(): string
    {
        return match ($this){
            self::Mon => "thứ 2",
            self::Tue => 'thứ 3',
            self::Wed => 'thứ 4',
            self::Thu => 'thứ 5',
            self::Fri => 'thứ 6',
            self::Sat => 'thứ 7',
            self::Sun => 'chủ nhật',
            default => dd($this)
        };
    }
}
