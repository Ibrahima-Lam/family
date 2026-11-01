<?php

declare(strict_types=1);

use App\Security\Auth;
use Core\ErrorHandler;
use Core\Http\Request;
use Core\Router;
use Core\Security\Csrf;
use Core\Support\Config;

require_once dirname(__DIR__) . '/vendor/autoload.php';

ErrorHandler::register();

date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

if (session_status() === PHP_SESSION_NONE) {
    $secure = is_https();

    session_name((string) Config::get('security.session_name', 'famille_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => (string) Config::get('security.session_same_site', 'Lax'),
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    session_start();
}

Csrf::bootstrap();

if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header_remove('X-Powered-By');

    foreach ((array) Config::get('security.headers', []) as $name => $value) {
        header($name . ': ' . $value);
    }

    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

$router = new Router(new Request());

$router->alias('csrf', static fn (string $argument = '') => static function (): void {
    Csrf::validate();
});

$router->alias('auth', static fn (string $argument = '') => static function (): void {
    Auth::requireLogin();
});

$router->alias('role', static fn (string $role = 'lecteur') => static function () use ($role): void {
    Auth::requireRole($role);
});

$router->alias('guest', static fn (string $argument = '') => static function (): void {
    Auth::redirectIfAuthenticated();
});

foreach (['web', 'api'] as $file) {
    $routes = require dirname(__DIR__) . '/routes/' . $file . '.php';
    foreach ($routes as $route) {
        $router->add(
            $route[0],
            $route[1],
            $route[2],
            $route[3] ?? []
        );
    }
}

return $router;
