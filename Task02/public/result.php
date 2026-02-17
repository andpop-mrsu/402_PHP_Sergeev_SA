<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$gameId = (int) ($_GET['id'] ?? ($_SESSION['game_id'] ?? 0));

if ($gameId <= 0) {
    redirect('index.php');
}

$game = $repo->getGame($gameId);
if (!$game) {
    render('error.php', ['message' => 'Игра не найдена.'], 'Task02 • Ошибка');
    exit;
}

$rounds = $repo->listRoundsForGame($gameId);

render('result.php', ['game' => $game, 'rounds' => $rounds], 'Task02 • Результат');

