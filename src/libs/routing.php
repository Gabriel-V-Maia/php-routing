<?php

class Router
{

    public function get(string $routeName, callable $callback)
    {
        $route = new Route($routeName, $callback);

        if (!$route) 
            throw new Error("Error while creating router!");
            

        return $route;

    }
}

class Route
{
    private string $routeName;
    private $callback;

    public function __construct(string $routeName, callable $callback)
    {
        $this->routeName = $routeName;
        $this->callback = $callback;
    }

    public function run()
    {
        call_user_func($this->callback);
    }
}

?>