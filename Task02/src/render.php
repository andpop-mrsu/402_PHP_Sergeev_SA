<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function render(string $viewFile, array $vars = [], string $title = 'Task02'): void
{
    extract($vars, EXTR_SKIP);

    ob_start();
    require project_root() . '/views/' . $viewFile;
    $content = (string) ob_get_clean();

    require project_root() . '/views/layout.php';
}

