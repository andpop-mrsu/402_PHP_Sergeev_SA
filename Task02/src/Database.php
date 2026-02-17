<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

final class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $path = db_path();

        // fail fast if PDO SQLite driver is missing
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException('Не найден драйвер PDO SQLite (pdo_sqlite). Установите/включите расширение SQLite в PHP.');
        }

        // ensure db directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->migrate();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    private function migrate(): void
    {
        $this->pdo->exec('PRAGMA foreign_keys = ON');

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS players (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL
            )'
        );

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                player_id INTEGER NOT NULL,
                status TEXT NOT NULL,
                total_rounds INTEGER NOT NULL,
                current_round INTEGER NOT NULL,
                correct_answers INTEGER NOT NULL,
                started_at TEXT NOT NULL,
                finished_at TEXT NULL,
                FOREIGN KEY (player_id) REFERENCES players(id)
            )'
        );

        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS rounds (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                game_id INTEGER NOT NULL,
                round_no INTEGER NOT NULL,
                a INTEGER NOT NULL,
                b INTEGER NOT NULL,
                correct_answer INTEGER NOT NULL,
                user_answer TEXT NULL,
                is_correct INTEGER NULL,
                created_at TEXT NOT NULL,
                answered_at TEXT NULL,
                UNIQUE(game_id, round_no),
                FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
            )'
        );

        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_games_player ON games(player_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_rounds_game ON rounds(game_id)');
    }
}

