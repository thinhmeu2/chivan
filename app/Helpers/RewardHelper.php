<?php

namespace App\Helpers;

class RewardHelper
{
    public static function getXsmb(): array
    {
        return [
            'mdb' => 'Mã đặc biệt',
            'g0' => 'Đặc bịệt',
            'g1' => 'Giải nhất',
            'g2' => 'Giải nhì',
            'g3' => 'Giải ba',
            'g4' => 'Giải tư',
            'g5' => 'Giải năm',
            'g6' => 'Giải sáu',
            'g7' => 'Giải bảy',
        ];
    }
    public static function getLottery(): array
    {
        return [
            'g0' => 'Đặc bịệt',
            'g1' => 'Giải nhất',
            'g2' => 'Giải nhì',
            'g3' => 'Giải ba',
            'g4' => 'Giải tư',
            'g5' => 'Giải năm',
            'g6' => 'Giải sáu',
            'g7' => 'Giải bảy',
            'g8' => 'Giải tám',
        ];
    }
}
