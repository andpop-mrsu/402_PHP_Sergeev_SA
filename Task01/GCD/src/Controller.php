<?php

namespace Kulebyaka1337\GCD\Controller;

use Kulebyaka1337\GCD\Database;

use function Kulebyaka1337\GCD\gcd;
use function Kulebyaka1337\GCD\View\showWelcome;
use function Kulebyaka1337\GCD\View\showQuestion;
use function Kulebyaka1337\GCD\View\showResult;

function startGame(): void
{
    $player = showWelcome();

    $a = rand(1, 100);
    $b = rand(1, 100);

    $answer = showQuestion($a, $b);
    $correct = gcd($a, $b);

    $isCorrect = ($answer === $correct);

    showResult($isCorrect, $correct);

    Database::saveGame(
        $player,
        $a,
        $b,
        $answer,
        $correct,
        $isCorrect
    );
}
