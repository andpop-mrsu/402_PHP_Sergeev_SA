<?php

declare(strict_types=1);

namespace Task03;

use PDO;
use RuntimeException;

final class Database
{
    public static function connect(string $dbPath): PDO
    {
        if (!extension_loaded('pdo_sqlite')) {
            throw new RuntimeException('Расширение pdo_sqlite не включено в PHP.');
        }

        $directory = dirname($dbPath);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Не удалось создать каталог базы данных.');
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        self::migrate($pdo);

        return $pdo;
    }

    private static function migrate(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                player_name TEXT NOT NULL,
                current_round INTEGER NOT NULL DEFAULT 1,
                total_rounds INTEGER NOT NULL DEFAULT 3,
                correct_answers INTEGER NOT NULL DEFAULT 0,
                is_finished INTEGER NOT NULL DEFAULT 0,
                current_a INTEGER,
                current_b INTEGER,
                created_at TEXT NOT NULL,
                finished_at TEXT
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS steps (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                game_id INTEGER NOT NULL,
                round_number INTEGER NOT NULL,
                number_a INTEGER NOT NULL,
                number_b INTEGER NOT NULL,
                user_answer INTEGER NOT NULL,
                correct_answer INTEGER NOT NULL,
                is_correct INTEGER NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
            )'
        );
    }
}
