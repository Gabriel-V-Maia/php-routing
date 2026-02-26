<?php

class routeExpection extends \Exception
{
    const DEFAULT_MESSAGE = "Something went wrong when creating a route!";
    
    public function __construct($message = self::DEFAULT_MESSAGE)
    {
        if (!empty($message))
            $message = DEFAULT_MESSAGE . $message; 
        
        parent::__construct($message);
    }
}

class Router
{
   protected array $routes = [];

    public function get(string $routeName, callable $callback)
    {
        $route = new Route($routeName,"GET", $callback);


        $this->routes[] = $route;
    } 

    public function dispatch(string $path, string $method)
    {
        $found = false;
        foreach ($this->routes as $route)
        {
            if ($path === $route->routeName && $method === $route->method)
            {
                $route->run(); 
                $found = true;
                break;
            }
        }
    
        if (!$found) {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}

class Route
{
    private const VALID_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];

    public string $routeName;
    public string $method;
    public $callback;

    public function __construct(string $routeName, string $method, callable $callback)
    {
        if (empty($routeName))
            throw new RouteException("Route name can't be empty.");

        $method = strtoupper($method);
        if (!in_array($method, self::VALID_METHODS))
            throw new RouteException("Invalid HTTP method '$method' for route '$routeName'.");

        $this->routeName = $routeName;
        $this->method = $method;
        $this->callback = $callback;
    }

    public function run()
    {
        call_user_func($this->callback);
    }
}

?>
