<?php
require_once '../autoloader/autoload.php';
$p = $_GET["p"] ?? "app/index";
$args = explode("/", $p);
$class = $args[0] ?? null;

$controller = "App\Controllers\\" . $class . "Controller";
$methode = $args[1] ?? "index";
$instance = new $controller;
unset($args[0], $args[1]);
try {
    call_user_func_array([$instance, $methode], $args ?? []);
} catch (\Exception $e) {
    echo $e->getMessage();
}
