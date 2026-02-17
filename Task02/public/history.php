<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$games = $repo->listLatestGames(20);

render('history.php', ['games' => $games], 'Task02 • История');

