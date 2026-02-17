<?php

declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function project_root(): string
{
    // .../Task02/public -> .../Task02
    return dirname(__DIR__);
}

function db_path(): string
{
    return project_root() . '/db/app.sqlite';
}

function now_iso(): string
{
    return (new DateTimeImmutable('now'))->format('c');
}

