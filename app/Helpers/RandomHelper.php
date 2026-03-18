<?php

namespace App\Helpers;

class RandomHelper
{
    static function getRandomDauSo(int|array $arr): array
    {
        // normalize về array
        $arr = is_array($arr) ? $arr : [$arr];

        $arrNumber = [];

        foreach ($arr as $item) {
            for ($i = 0; $i <= 9; $i++) {
                $arrNumber[] = (string) $item . $i;
            }
        }

        return $arrNumber;
    }

    public function getRandomLokep(int $count = 1): array
    {
        if ($count > 10)
            throw new \LogicException("Chỉ có 10 số lô kép thôi!");
        $list = ['00', '11', '22', '33', '44', '55', '66', '77', '88', '99'];
        shuffle($list);
        return array_slice($list, 0, $count);
    }

    public function getRandomSTL(int $count = 1): array
    {
        $pairs = [];

        // tạo tất cả cặp AB với A < B để tránh trùng [AB, BA] vs [BA, AB]
        for ($a = 0; $a <= 9; $a++) {
            for ($b = $a + 1; $b <= 9; $b++) {
                $pairs[] = [
                    $a . $b,
                    $b . $a,
                ];
            }
        }

        // giới hạn count
        $count = min($count, count($pairs));

        // random không trùng
        shuffle($pairs);

        return array_slice($pairs, 0, $count);
    }

    public function getRandomNumber(int $numberLength = 2, int $count = 2): array {
        $min = 0;
        $max = (10 ** $numberLength) - 1;

        $total = $max - $min + 1;
        $count = min($count, $total);

        $result = [];
        $used = [];

        while (count($result) < $count) {
            $num = random_int($min, $max);

            if (isset($used[$num])) {
                continue;
            }

            $used[$num] = true;

            // giữ đúng độ dài bằng cách pad
            $result[] = str_pad((string) $num, $numberLength, '0', STR_PAD_LEFT);
        }

        return $result;
    }
}
