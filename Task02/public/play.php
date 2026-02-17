<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$gameId = (int) ($_SESSION['game_id'] ?? 0);

if ($gameId <= 0) {
    redirect('index.php');
}

$game = $repo->getGame($gameId);
if (!$game) {
    unset($_SESSION['game_id']);
    redirect('index.php');
}

if ($game['status'] !== 'in_progress') {
    redirect('result.php?id=' . (int) $gameId);
}

$flash = null;

// answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roundNo = (int) ($_POST['round_no'] ?? 0);
    $answer = (string) ($_POST['answer'] ?? '');

    try {
        $res = $repo->answerRound($gameId, $roundNo, $answer);

        if ($res['is_correct']) {
            $flash = 'Верно! 👍';
        } else {
            $flash = 'Неверно. Правильный ответ: ' . (int) $res['correct_answer'];
        }

        // reload updated game
        $game = $repo->getGame($gameId);
        if (!$game) {
            throw new RuntimeException('Игра не найдена после ответа.');
        }

        if ((int) $game['current_round'] > (int) $game['total_rounds']) {
            $repo->finishGame($gameId);
            redirect('result.php?id=' . (int) $gameId);
        }
    } catch (Throwable $e) {
        render('error.php', ['message' => $e->getMessage()], 'Task02 • Ошибка');
        exit;
    }
}

// determine current round and ensure it's created
$currentRoundNo = (int) $game['current_round'];

$round = $repo->getRound($gameId, $currentRoundNo);
if (!$round) {
    $data = GcdGame::makeRound();
    $repo->createRound($gameId, $currentRoundNo, $data['a'], $data['b'], $data['answer']);
    $round = $repo->getRound($gameId, $currentRoundNo);
}

if (!$round) {
    render('error.php', ['message' => 'Не удалось создать раунд.'], 'Task02 • Ошибка');
    exit;
}

render('play.php', ['game' => $game, 'round' => $round, 'flash' => $flash], 'Task02 • Игра');

