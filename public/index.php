<?php

declare(strict_types=1);

/** @var \Core\Router $router */
$router = require dirname(__DIR__) . '/bootstrap/app.php';

$router->dispatch();
