<?php

namespace App\Enums;

enum ResultPrizeCodeEnum: string
{
    case Madb = 'madb';
    case G0 = 'g0';
    case G1 = 'g1';
    case G2 = 'g2';
    case G3 = 'g3';
    case G4 = 'g4';
    case G5 = 'g5';
    case G6 = 'g6';
    case G7 = 'g7';
    case G8 = 'g8';
    case DrawingPeriod = 'drawing-period';

    case CountWin1 = 'count-win1';
    case CountWin2 = 'count-win2';
    case CountWin3 = 'count-win3';
    case CountWin4 = 'count-win4';
    case CountWin5 = 'count-win5';
    case CountWin6 = 'count-win6';
    case CountWin7 = 'count-win7';
    case CountWin8 = 'count-win8';
    case CountWin9 = 'count-win9';
    case CountWin10 = 'count-win10';
    case CountWin11 = 'count-win11';
    case MoneyWin1 = 'money-win1';
    case MoneyWin2 = 'money-win2';

    public function getTitle(?string $code = null): string
    {
        return match ($this) {
            self::Madb => 'Mã DB',

            self::G0 => 'Đ.Biệt',
            self::G1 => 'G.1',
            self::G2 => 'G.2',
            self::G3 => 'G.3',
            self::G4 => 'G.4',
            self::G5 => 'G.5',
            self::G6 => 'G.6',
            self::G7 => 'G.7',
            self::G8 => 'G.8',

            self::DrawingPeriod => 'Kỳ quay',
        };
    }
    public static function getPrizeLoto(): array
    {
        $arr = [self::G0, self::G1, self::G2, self::G3, self::G4, self::G5, self::G6, self::G7, self::G8];
        return array_map(fn($i) => $i->value, $arr);
    }
    public static function getPrizeSpin(string $code): array
    {
        if (strtolower($code) == 'xsmb')
            $arr = [self::G0, self::G1, self::G2, self::G3, self::G4, self::G5, self::G6, self::G7];
        else {
            $arr = [self::G0, self::G1, self::G2, self::G3, self::G4, self::G5, self::G6, self::G7, self::G8];
            $arr = array_reverse($arr);
        }
        return array_map(fn($i) => $i->value, $arr);
    }
}
