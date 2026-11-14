<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $view, array $data = []): void
    {
        $file = dirname(__DIR__) . "/Views/{$view}.php";
        if (!is_file($file)) {
            throw new \RuntimeException("View tidak ditemukan: {$view}");
        }
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/layout/header.php';
        require $file;
        require dirname(__DIR__) . '/Views/layout/footer.php';
    }

    public static function e(?string $text): string
    {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $page): never
    {
        header("Location: index.php?page={$page}");
        exit;
    }
}
