<?php

namespace FlexiAPI\Core;

use Closure;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, Closure $callback): void
    {
        $this->routes[$method][$path] = $callback;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if (isset($this->routes[$method][$path])) {
            $this->routes[$method][$path]();
            return;
        }
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => 'Route not found']);
    }
}
