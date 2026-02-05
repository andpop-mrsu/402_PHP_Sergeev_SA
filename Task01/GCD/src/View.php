<?php

namespace Kulebyaka1337\GCD\View;

use function cli\line;
use function cli\prompt;

function showWelcome(): string
{
    line('=== Игра "Наибольший общий делитель" ===');
    return prompt('Введите ваше имя');
}

function showQuestion(int $a, int $b): int
{
    return (int) prompt("Найдите НОД чисел {$a} и {$b}");
}

function showResult(bool $isCorrect, int $correctGcd): void
{
    if ($isCorrect) {
        line('Верно!');
    } else {
        line("Неверно. Правильный ответ: {$correctGcd}");
    }
}
