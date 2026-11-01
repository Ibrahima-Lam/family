<?php

namespace Core\Http;

use Core\Support\Config;

final class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    public static function redirect(string $url, int $status = 302): never
    {
        if (!headers_sent()) {
            header('Location: ' . $url, true, $status);
        }
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: text/html; charset=utf-8');
        }

        $title = match ($status) {
            403 => 'Accès refusé',
            404 => 'Page introuvable',
            419 => 'Session expirée',
            429 => 'Trop de tentatives',
            default => 'Erreur',
        };

        if (($message === '') && !Config::get('app.debug')) {
            $message = $title;
        }

        $view = dirname(__DIR__, 2) . '/app/Views/errors/error.php';
        if (is_file($view)) {
            require $view;
        } else {
            echo '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
            echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        exit;
    }
}
