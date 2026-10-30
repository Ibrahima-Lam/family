<?php

namespace Core\Controllers;


class Controller
{

    public static function loadModel($class): mixed
    {
        $model = "App\\Models\\" . ucfirst($class);
        return new $model();
    }

    public function render(string $file, $data = []): void
    {
        extract($data);
        ob_start();
        require dirname(__DIR__, 2) . "/app/Views/$file.php";
        $content = ob_get_clean();
        require_once dirname(__DIR__, 2) . "/public/layout.php";
    }

    public function response(mixed $data): void
    {
        if (is_scalar($data)) echo $data;
        else echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit();
    }
    public function redirect($url): void
    {
        header('Location: ' . $url);
        exit();
    }
}
