<?php

declare(strict_types=1);

class RouteException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class Route
{
    private const VALID_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];

    public readonly string $path;
    public readonly string $method;
    private readonly mixed $callback;
    private array $paramNames = [];
    private string $regex;

    public function __construct(string $path, string $method, callable $callback)
    {
        if (empty($path))
            throw new RouteException("Route path can't be empty.");

        $method = strtoupper($method);
        if (!in_array($method, self::VALID_METHODS, true))
            throw new RouteException("Invalid HTTP method '$method' for route '$path'.");

        $this->path     = $path;
        $this->method   = $method;
        $this->callback = $callback;
        $this->regex    = $this->buildRegex($path);
    }

    private function buildRegex(string $path): string
    {
        $pattern = preg_replace_callback('/:([a-zA-Z_][a-zA-Z0-9_]*)/', function ($matches) {
            $this->paramNames[] = $matches[1];
            return '([^/]+)';
        }, $path);

        return '#^' . $pattern . '$#';
    }

    public function matches(string $path): bool
    {
        return (bool) preg_match($this->regex, $path);
    }

    public function extractParams(string $path): array
    {
        preg_match($this->regex, $path, $matches);
        array_shift($matches);
        return array_combine($this->paramNames, $matches) ?: [];
    }

    public function run(array $params = []): void
    {
        call_user_func($this->callback, $params);
    }
}

class Router
{
    /** @var Route[] */
    protected array $routes = [];

    private function addRoute(string $path, string $method, callable $callback): self
    {
        $this->routes[] = new Route($path, $method, $callback);
        return $this;
    }

    public function get(string $path, callable $callback): self
    {
        return $this->addRoute($path, 'GET', $callback);
    }

    public function post(string $path, callable $callback): self
    {
        return $this->addRoute($path, 'POST', $callback);
    }

    public function put(string $path, callable $callback): self
    {
        return $this->addRoute($path, 'PUT', $callback);
    }

    public function patch(string $path, callable $callback): self
    {
        return $this->addRoute($path, 'PATCH', $callback);
    }

    public function delete(string $path, callable $callback): self
    {
        return $this->addRoute($path, 'DELETE', $callback);
    }

    public function dispatch(string $path, string $method): void
    {
        $method = strtoupper($method);
        $path   = rtrim(parse_url($path, PHP_URL_PATH), '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route->method === $method && $route->matches($path)) {
                $params = $route->extractParams($path);
                $route->run($params);
                return;
            }
        }

        $pathExists = array_filter($this->routes, fn(Route $r) => $r->matches($path));

        if (!empty($pathExists)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_map(fn(Route $r) => $r->method, $pathExists)));
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path   = $_SERVER['REQUEST_URI']    ?? '/';
        $this->dispatch($path, $method);
    }
}
