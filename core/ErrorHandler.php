<?php

namespace Core;

use Core\Exceptions\HttpException;
use Core\Http\Response;
use Core\Support\Config;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', Config::get('app.debug') ? '1' : '0');
        ini_set('log_errors', '1');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
                error_log($message . ' in ' . $file . ':' . $line);

                return true;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (Throwable $exception): void {
            self::log($exception);

            if ($exception instanceof HttpException) {
                if (self::wantsJson()) {
                    Response::json([
                        'status' => 0,
                        'message' => $exception->getMessage(),
                    ], $exception->statusCode());
                }

                Response::abort($exception->statusCode(), $exception->getMessage());
            }

            $message = Config::get('app.debug')
                ? $exception->getMessage()
                : 'Une erreur interne est survenue.';

            Response::abort(500, $message);
        });
    }

    private static function wantsJson(): bool
    {
        $path = $_GET['p'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return (is_string($path) && str_starts_with($path, 'api/'))
            || (is_string($accept) && str_contains($accept, 'application/json'));
    }

    private static function log(Throwable $exception): void
    {
        $dir = dirname(__DIR__) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
