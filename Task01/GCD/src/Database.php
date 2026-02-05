<?php

namespace Kulebyaka1337\GCD;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    private static function connect(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO('sqlite:' . __DIR__ . '/../gcd.sqlite');
            self::$pdo->exec(
                'CREATE TABLE IF NOT EXISTS games (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    player TEXT,
                    a INTEGER,
                    b INTEGER,
                    answer INTEGER,
                    correct INTEGER,
                    result INTEGER,
                    created_at TEXT
                )'
            );
        }
        return self::$pdo;
    }

    public static function saveGame(
        string $player,
        int $a,
        int $b,
        int $answer,
        int $correct,
        bool $result
    ): void {
        $stmt = self::connect()->prepare(
            'INSERT INTO games VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $player,
            $a,
            $b,
            $answer,
            $correct,
            $result ? 1 : 0,
            date('Y-m-d H:i:s')
        ]);
    }
}
