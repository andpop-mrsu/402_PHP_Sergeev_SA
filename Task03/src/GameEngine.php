<?php

declare(strict_types=1);

namespace Task03;

final class GameEngine
{
    public static function generateQuestion(): array
    {
        $a = random_int(1, 100);
        $b = random_int(1, 100);

        return [
            'a' => $a,
            'b' => $b,
            'question' => sprintf('Найдите НОД чисел %d и %d', $a, $b),
        ];
    }

    public static function gcd(int $a, int $b): int
    {
        $a = abs($a);
        $b = abs($b);

        while ($b !== 0) {
            $tmp = $b;
            $b = $a % $b;
            $a = $tmp;
        }

        return $a;
    }
}
