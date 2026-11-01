<?php

use Core\Support\Config;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('person_name')) {
    function person_name(mixed $name, mixed $surname = null, bool $ucwords = false): string
    {
        $name = trim((string) ($name ?? ''));
        $surname = trim((string) ($surname ?? ''));

        if ($ucwords) {
            $name = ucwords($name);
            $surname = ucwords($surname);
        }

        $html = e($name);

        if ($surname !== '') {
            $html .= ' <span class="person-surname" title="Surnom">' . e($surname) . '</span>';
        }

        return $html;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('is_https')) {
    function is_https(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \Core\Security\Csrf::field();
    }
}
