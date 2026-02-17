<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = (string) ($_POST['name'] ?? '');
        $playerId = $repo->upsertPlayer($name);

        $gameId = $repo->createGame($playerId);

        $_SESSION['game_id'] = $gameId;

        redirect('play.php');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

render('home.php', ['error' => $error], 'Task02 • Главная');

