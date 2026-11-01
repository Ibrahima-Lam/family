<?php

namespace Core\Http;

final class Request
{
    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $override = $_POST['_method'] ?? null;

        if ($method === 'POST' && is_string($override)) {
            $override = strtoupper($override);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        return $method;
    }

    public function path(): string
    {
        $path = $_GET['p'] ?? '';
        $path = is_string($path) ? $path : '';
        $path = trim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?: '';

        return $path;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }

        return $default;
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return isset($_SERVER[$key]) ? (string) $_SERVER[$key] : null;
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function expectsJson(): bool
    {
        $accept = $this->header('Accept') ?? '';
        $requestedWith = $this->header('X-Requested-With') ?? '';

        return str_contains($accept, 'application/json')
            || $requestedWith === 'XMLHttpRequest'
            || str_starts_with($this->path(), 'api/');
    }
}
