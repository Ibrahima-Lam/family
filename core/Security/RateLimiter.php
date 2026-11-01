<?php

namespace Core\Security;

final class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $data = self::read($key, $decaySeconds);

        return count($data) >= $maxAttempts;
    }

    public static function hit(string $key, int $decaySeconds): void
    {
        $data = self::read($key, $decaySeconds);
        $data[] = time();
        self::write($key, $data);
    }

    public static function clear(string $key): void
    {
        $file = self::path($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    public static function availableIn(string $key, int $maxAttempts, int $decaySeconds): int
    {
        $data = self::read($key, $decaySeconds);
        if (count($data) < $maxAttempts) {
            return 0;
        }

        sort($data);
        $oldest = $data[count($data) - $maxAttempts];

        return max(0, ($oldest + $decaySeconds) - time());
    }

    /** @return array<int, int> */
    private static function read(string $key, int $decaySeconds): array
    {
        $file = self::path($key);
        if (!is_file($file)) {
            return [];
        }

        $handle = @fopen($file, 'rb');
        if ($handle === false) {
            return [];
        }

        flock($handle, LOCK_SH);
        $contents = stream_get_contents($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        $data = json_decode((string) $contents, true);
        if (!is_array($data)) {
            return [];
        }

        $min = time() - $decaySeconds;

        return array_values(array_filter($data, static fn ($timestamp) => is_int($timestamp) && $timestamp >= $min));
    }

    /** @param array<int, int> $data */
    private static function write(string $key, array $data): void
    {
        $file = self::path($key);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $handle = @fopen($file, 'cb');
        if ($handle === false) {
            return;
        }

        flock($handle, LOCK_EX);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode(array_values($data)));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private static function path(string $key): string
    {
        return dirname(__DIR__, 2) . '/storage/cache/rate_' . hash('sha256', $key) . '.json';
    }
}
