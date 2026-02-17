<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/render.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/GcdGame.php';
require_once __DIR__ . '/Repo.php';

try {
    $db = new Database();
    $repo = new Repo($db);
} catch (Throwable $e) {
    render('error.php', ['message' => $e->getMessage()], 'Task02 • Ошибка');
    exit;
}
