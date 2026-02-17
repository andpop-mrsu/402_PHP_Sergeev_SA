<?php

declare(strict_types=1);

final class GcdGame
{
    public const TOTAL_ROUNDS = 3;

    public static function description(): string
    {
        return 'Найдите НОД (наибольший общий делитель) двух чисел.';
    }

    /**
     * @return array{a:int,b:int,answer:int}
     */
    public static function makeRound(): array
    {
        $a = random_int(10, 99);
        $b = random_int(10, 99);

        return ['a' => $a, 'b' => $b, 'answer' => self::gcd($a, $b)];
    }

    private static function gcd(int $a, int $b): int
    {
        $a = abs($a);
        $b = abs($b);

        while ($b !== 0) {
            $tmp = $a % $b;
            $a = $b;
            $b = $tmp;
        }

        return $a;
    }
}

