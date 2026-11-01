<?php

namespace Core\Controllers;

use Core\Http\Response;

class Controller
{
    public static function loadModel(string $class): mixed
    {
        $model = 'App\\Models\\' . ucfirst($class);

        return new $model();
    }

    public function render(string $file, array $data = []): void
    {
        extract($data);
        ob_start();
        require dirname(__DIR__, 2) . '/app/Views/' . $file . '.php';
        $content = ob_get_clean();
        require dirname(__DIR__, 2) . '/public/layout.php';
    }

    public function response(mixed $data, int $status = 200): never
    {
        if (is_scalar($data)) {
            http_response_code($status);
            echo $data;
            exit;
        }

        Response::json($data, $status);
    }

    public function redirect(string $url, int $status = 302): never
    {
        Response::redirect($url, $status);
    }

    public function abort(int $status, string $message = ''): never
    {
        Response::abort($status, $message);
    }
}
