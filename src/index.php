<?php
require_once "libs/routing.php";

$router = new Router();

$index = function()
{
    http_response_code(200);
    header("Content-Type: text/html; charset=UTF-8");
    include('pages/index.html');
};

$router->get("/", $index);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    $router->dispatch($requestUri, $_SERVER['REQUEST_METHOD']);
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
