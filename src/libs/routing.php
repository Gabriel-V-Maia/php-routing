<?php

class Router
{
   protected array $routes = [];

    public function get(string $routeName, callable $callback)
    {
        $route = new Route($routeName,"GET", $callback);

        if (!$route) 
            throw new Error("Error while creating router!");
            

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
    public string $routeName;
    public string $method;
    public $callback;

    public function __construct(string $routeName, $method,callable $callback)
    {
        $this->routeName = $routeName;
        $this->callback = $callback;
        $this->method = $method;
    }

    public function run()
    {
        call_user_func($this->callback);
    }
}

?>