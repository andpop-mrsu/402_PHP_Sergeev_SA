<?php

declare(strict_types=1);

namespace Task03;

use PDO;
use RuntimeException;

final class GameRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createGame(string $playerName): array
    {
        $question = GameEngine::generateQuestion();
        $createdAt = date('c');

        $statement = $this->pdo->prepare(
            'INSERT INTO games (
                player_name,
                current_round,
                total_rounds,
                correct_answers,
                is_finished,
                current_a,
                current_b,
                created_at,
                finished_at
            ) VALUES (
                :player_name,
                1,
                3,
                0,
                0,
                :current_a,
                :current_b,
                :created_at,
                NULL
            )'
        );

        $statement->execute([
            ':player_name' => $playerName,
            ':current_a' => $question['a'],
            ':current_b' => $question['b'],
            ':created_at' => $createdAt,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return [
            'id' => $id,
            'playerName' => $playerName,
            'currentRound' => 1,
            'totalRounds' => 3,
            'question' => $question['question'],
            'numbers' => [
                'a' => $question['a'],
                'b' => $question['b'],
            ],
            'createdAt' => $createdAt,
        ];
    }

    public function getGames(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                id,
                player_name,
                current_round,
                total_rounds,
                correct_answers,
                is_finished,
                created_at,
                finished_at
             FROM games
             ORDER BY id DESC'
        );

        $games = [];
        foreach ($statement->fetchAll() as $row) {
            $games[] = $this->mapGameRow($row);
        }

        return $games;
    }

    public function getGame(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                player_name,
                current_round,
                total_rounds,
                correct_answers,
                is_finished,
                current_a,
                current_b,
                created_at,
                finished_at
             FROM games
             WHERE id = :id'
        );
        $statement->execute([':id' => $id]);
        $game = $statement->fetch();

        if ($game === false) {
            return null;
        }

        $stepStatement = $this->pdo->prepare(
            'SELECT
                id,
                round_number,
                number_a,
                number_b,
                user_answer,
                correct_answer,
                is_correct,
                created_at
             FROM steps
             WHERE game_id = :game_id
             ORDER BY round_number ASC, id ASC'
        );
        $stepStatement->execute([':game_id' => $id]);

        $steps = [];
        foreach ($stepStatement->fetchAll() as $stepRow) {
            $steps[] = [
                'id' => (int) $stepRow['id'],
                'roundNumber' => (int) $stepRow['round_number'],
                'numbers' => [
                    'a' => (int) $stepRow['number_a'],
                    'b' => (int) $stepRow['number_b'],
                ],
                'userAnswer' => (int) $stepRow['user_answer'],
                'correctAnswer' => (int) $stepRow['correct_answer'],
                'isCorrect' => (bool) $stepRow['is_correct'],
                'createdAt' => $stepRow['created_at'],
            ];
        }

        $result = $this->mapGameRow($game);
        $result['steps'] = $steps;

        if (!(bool) $game['is_finished']) {
            $result['currentQuestion'] = [
                'question' => sprintf(
                    'Найдите НОД чисел %d и %d',
                    (int) $game['current_a'],
                    (int) $game['current_b']
                ),
                'numbers' => [
                    'a' => (int) $game['current_a'],
                    'b' => (int) $game['current_b'],
                ],
            ];
        }

        return $result;
    }

    public function saveStep(int $gameId, int $answer): array
    {
        $game = $this->findRawGame($gameId);
        if ($game === null) {
            throw new RuntimeException('Игра не найдена.');
        }
        if ((int) $game['is_finished'] === 1) {
            throw new RuntimeException('Игра уже завершена.');
        }

        $roundNumber = (int) $game['current_round'];
        $a = (int) $game['current_a'];
        $b = (int) $game['current_b'];
        $correctAnswer = GameEngine::gcd($a, $b);
        $isCorrect = $answer === $correctAnswer;
        $correctAnswers = (int) $game['correct_answers'] + ($isCorrect ? 1 : 0);
        $createdAt = date('c');

        $this->pdo->beginTransaction();

        try {
            $insertStep = $this->pdo->prepare(
                'INSERT INTO steps (
                    game_id,
                    round_number,
                    number_a,
                    number_b,
                    user_answer,
                    correct_answer,
                    is_correct,
                    created_at
                ) VALUES (
                    :game_id,
                    :round_number,
                    :number_a,
                    :number_b,
                    :user_answer,
                    :correct_answer,
                    :is_correct,
                    :created_at
                )'
            );

            $insertStep->execute([
                ':game_id' => $gameId,
                ':round_number' => $roundNumber,
                ':number_a' => $a,
                ':number_b' => $b,
                ':user_answer' => $answer,
                ':correct_answer' => $correctAnswer,
                ':is_correct' => $isCorrect ? 1 : 0,
                ':created_at' => $createdAt,
            ]);

            $totalRounds = (int) $game['total_rounds'];

            if ($roundNumber >= $totalRounds) {
                $updateGame = $this->pdo->prepare(
                    'UPDATE games
                     SET correct_answers = :correct_answers,
                         is_finished = 1,
                         finished_at = :finished_at,
                         current_a = NULL,
                         current_b = NULL
                     WHERE id = :id'
                );
                $updateGame->execute([
                    ':correct_answers' => $correctAnswers,
                    ':finished_at' => $createdAt,
                    ':id' => $gameId,
                ]);

                $this->pdo->commit();

                return [
                    'status' => 'finished',
                    'step' => [
                        'roundNumber' => $roundNumber,
                        'numbers' => ['a' => $a, 'b' => $b],
                        'userAnswer' => $answer,
                        'correctAnswer' => $correctAnswer,
                        'isCorrect' => $isCorrect,
                    ],
                    'summary' => [
                        'gameId' => $gameId,
                        'playerName' => $game['player_name'],
                        'correctAnswers' => $correctAnswers,
                        'totalRounds' => $totalRounds,
                        'isWin' => $correctAnswers === $totalRounds,
                    ],
                ];
            }

            $nextRound = $roundNumber + 1;
            $nextQuestion = GameEngine::generateQuestion();

            $updateGame = $this->pdo->prepare(
                'UPDATE games
                 SET current_round = :current_round,
                     correct_answers = :correct_answers,
                     current_a = :current_a,
                     current_b = :current_b
                 WHERE id = :id'
            );
            $updateGame->execute([
                ':current_round' => $nextRound,
                ':correct_answers' => $correctAnswers,
                ':current_a' => $nextQuestion['a'],
                ':current_b' => $nextQuestion['b'],
                ':id' => $gameId,
            ]);

            $this->pdo->commit();

            return [
                'status' => 'continue',
                'step' => [
                    'roundNumber' => $roundNumber,
                    'numbers' => ['a' => $a, 'b' => $b],
                    'userAnswer' => $answer,
                    'correctAnswer' => $correctAnswer,
                    'isCorrect' => $isCorrect,
                ],
                'nextQuestion' => [
                    'roundNumber' => $nextRound,
                    'question' => $nextQuestion['question'],
                    'numbers' => [
                        'a' => $nextQuestion['a'],
                        'b' => $nextQuestion['b'],
                    ],
                ],
                'progress' => [
                    'correctAnswers' => $correctAnswers,
                    'totalRounds' => $totalRounds,
                ],
            ];
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    private function findRawGame(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM games WHERE id = :id');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    private function mapGameRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'playerName' => $row['player_name'],
            'currentRound' => (int) $row['current_round'],
            'totalRounds' => (int) $row['total_rounds'],
            'correctAnswers' => (int) $row['correct_answers'],
            'isFinished' => (bool) $row['is_finished'],
            'createdAt' => $row['created_at'],
            'finishedAt' => $row['finished_at'] ?? null,
        ];
    }
}
