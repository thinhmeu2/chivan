<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function feFormat(Carbon|string $date): string
    {
        if (is_string($date))
            $date = Carbon::createFromFormat('Y-m-d', $date);
        return $date->format(config('app.date_format'));
    }

    public static function today(): Carbon
    {
        $now = now();

        $changeHour = (int) config('app.time_change_day');

        if ($now->hour >= $changeHour) {
            $now->addDay();
        }

        return $now;
    }
    public static function dateNToday(): int
    {
        $now = now();

        $changeHour = (int) config('app.time_change_day');

        if ($now->hour >= $changeHour) {
            $now = $now->addDay();
        }

        return $now->format('N');
    }
    public static function dateNYesterday(): int
    {
        $now = now();

        $changeHour = (int) config('app.time_change_day');

        $days = $now->hour <= $changeHour ? 1 : 0;

        return $now->subDays($days)->format('N');
    }
}
