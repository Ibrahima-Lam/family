<?php

namespace Core\Controllers;



class Controller
{

    public static function loadModel($class): mixed
    {
        $model = "App\Modele\\$class";
        return new $model();
    }

    public function render(string $file, $data = []): void
    {
        extract($data);
        ob_start();
        require "../App/views/$file.php";
        $content = ob_get_clean();
        require_once "layout.php";
    }

    public function response(mixed $data): void
    {
        if (is_scalar($data)) echo $data;
        else echo json_encode($data, true);
        exit();
    }
    public function redirect($url): void
    {
        header('location:' . $url);
        exit();
    }
}
