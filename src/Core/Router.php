<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router sederhana berbasis query string: index.php?page=catalog
 */
class Router
{
    /** @var array<string, callable> */
    private array $routes = [];

    public function add(string $page, callable $handler): self
    {
        $this->routes[$page] = $handler;
        return $this;
    }

    public function dispatch(string $page): void
    {
        if (!isset($this->routes[$page])) {
            http_response_code(404);
            View::render('404');
            return;
        }
        ($this->routes[$page])();
    }
}
