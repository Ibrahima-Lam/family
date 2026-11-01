<?php

namespace Core\Security;

use Core\Http\Response;
use Core\Support\Config;

final class Csrf
{
    public static function bootstrap(): void
    {
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
    }

    public static function token(): string
    {
        self::bootstrap();

        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::fieldName() . '" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
    }

    public static function fieldName(): string
    {
        return (string) Config::get('security.csrf_field', '_token');
    }

    public static function rotate(): void
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    public static function validate(?string $token = null): void
    {
        if ($token === null) {
            $posted = $_POST[self::fieldName()] ?? null;
            $token = is_string($posted) && $posted !== ''
                ? $posted
                : ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        }

        if (!is_string($token) || $token === '' || !hash_equals(self::token(), $token)) {
            $path = $_GET['p'] ?? '';
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

            if ((is_string($path) && str_starts_with($path, 'api/'))
                || (is_string($accept) && str_contains($accept, 'application/json'))
            ) {
                Response::json([
                    'status' => 0,
                    'message' => 'Requête invalide (CSRF)',
                ], 419);
            }

            Response::abort(419, 'Votre session a expiré. Rechargez la page puis réessayez.');
        }
    }
}
