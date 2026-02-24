<?php
require_once "libs/routing.php";

$Router = new Router();

$index = function()
{
    http_response_code(200);
    header("Content-Type: text/html; charset=UTF-8");
    include('index.html');
};

$Router->get("/", $index);
?>