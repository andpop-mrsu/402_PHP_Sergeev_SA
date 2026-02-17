<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

final class Repo
{
    public function __construct(private Database $db)
    {
    }

    public function upsertPlayer(string $name): int
    {
        $name = trim($name);
        if ($name === '') {
            throw new RuntimeException('Имя не может быть пустым.');
        }

        $pdo = $this->db->pdo();

        $stmt = $pdo->prepare('SELECT id FROM players WHERE name = :name');
        $stmt->execute([':name' => $name]);
        $existing = $stmt->fetch();
        if ($existing) {
            return (int) $existing['id'];
        }

        $stmt = $pdo->prepare('INSERT INTO players (name, created_at) VALUES (:name, :created_at)');
        $stmt->execute([':name' => $name, ':created_at' => now_iso()]);
        return (int) $pdo->lastInsertId();
    }

    public function createGame(int $playerId): int
    {
        $pdo = $this->db->pdo();

        $stmt = $pdo->prepare(
            'INSERT INTO games (player_id, status, total_rounds, current_round, correct_answers, started_at, finished_at)
             VALUES (:player_id, :status, :total_rounds, :current_round, :correct_answers, :started_at, :finished_at)'
        );
        $stmt->execute([
            ':player_id' => $playerId,
            ':status' => 'in_progress',
            ':total_rounds' => GcdGame::TOTAL_ROUNDS,
            ':current_round' => 1,
            ':correct_answers' => 0,
            ':started_at' => now_iso(),
            ':finished_at' => null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function getGame(int $gameId): ?array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT g.*, p.name AS player_name
             FROM games g
             JOIN players p ON p.id = g.player_id
             WHERE g.id = :id'
        );
        $stmt->execute([':id' => $gameId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getRound(int $gameId, int $roundNo): ?array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT * FROM rounds WHERE game_id = :game_id AND round_no = :round_no'
        );
        $stmt->execute([':game_id' => $gameId, ':round_no' => $roundNo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createRound(int $gameId, int $roundNo, int $a, int $b, int $answer): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT OR IGNORE INTO rounds (game_id, round_no, a, b, correct_answer, user_answer, is_correct, created_at, answered_at)
             VALUES (:game_id, :round_no, :a, :b, :correct_answer, NULL, NULL, :created_at, NULL)'
        );
        $stmt->execute([
            ':game_id' => $gameId,
            ':round_no' => $roundNo,
            ':a' => $a,
            ':b' => $b,
            ':correct_answer' => $answer,
            ':created_at' => now_iso(),
        ]);
    }

    /**
     * @return array{is_correct:bool, correct_answer:int}
     */
    public function answerRound(int $gameId, int $roundNo, string $userAnswer): array
    {
        $pdo = $this->db->pdo();

        $round = $this->getRound($gameId, $roundNo);
        if (!$round) {
            throw new RuntimeException('Раунд не найден.');
        }
        if ($round['answered_at'] !== null) {
            // already answered - idempotent
            return ['is_correct' => (bool) $round['is_correct'], 'correct_answer' => (int) $round['correct_answer']];
        }

        $isCorrect = ((string) (int) $round['correct_answer']) === trim($userAnswer);

        $stmt = $pdo->prepare(
            'UPDATE rounds
             SET user_answer = :user_answer, is_correct = :is_correct, answered_at = :answered_at
             WHERE game_id = :game_id AND round_no = :round_no'
        );
        $stmt->execute([
            ':user_answer' => trim($userAnswer),
            ':is_correct' => $isCorrect ? 1 : 0,
            ':answered_at' => now_iso(),
            ':game_id' => $gameId,
            ':round_no' => $roundNo,
        ]);

        $stmt = $pdo->prepare(
            'UPDATE games
             SET correct_answers = correct_answers + :delta, current_round = current_round + 1
             WHERE id = :id'
        );
        $stmt->execute([
            ':delta' => $isCorrect ? 1 : 0,
            ':id' => $gameId,
        ]);

        return ['is_correct' => $isCorrect, 'correct_answer' => (int) $round['correct_answer']];
    }

    public function finishGame(int $gameId): void
    {
        $stmt = $this->db->pdo()->prepare(
            'UPDATE games
             SET status = :status, finished_at = :finished_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':status' => 'finished',
            ':finished_at' => now_iso(),
            ':id' => $gameId,
        ]);
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    public function listLatestGames(int $limit = 20): array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT g.id, p.name AS player_name, g.status, g.correct_answers, g.total_rounds, g.started_at, g.finished_at
             FROM games g
             JOIN players p ON p.id = g.player_id
             ORDER BY g.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    public function listRoundsForGame(int $gameId): array
    {
        $stmt = $this->db->pdo()->prepare(
            'SELECT * FROM rounds WHERE game_id = :game_id ORDER BY round_no ASC'
        );
        $stmt->execute([':game_id' => $gameId]);
        return $stmt->fetchAll();
    }
}

