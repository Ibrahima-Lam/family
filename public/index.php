<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$p = $_GET['p'] ?? 'app/index';
$args = explode('/', $p);
$class = ucfirst($args[0] ?? 'app');

$controller = 'App\\Controllers\\' . $class . 'Controller';
$method = $args[1] ?? 'index';
unset($args[0], $args[1]);

try {
    $instance = new $controller();
    call_user_func_array([$instance, $method], array_values($args));
} catch (\Throwable $e) {
    echo $e->getMessage();
}
