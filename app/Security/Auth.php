<?php

namespace App\Security;

use App\Models\Utilisateur;
use Core\Http\Response;
use Core\Security\Csrf;
use Core\Support\Config;

final class Auth
{
    private const SESSION_KEY = 'auth_user';

    private const LEVELS = [
        'lecteur' => 1,
        'editeur' => 2,
        'admin' => 3,
    ];

    public static function user(): ?array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;

        return is_array($user) ? $user : null;
    }

    public static function check(): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        $lifetime = (int) Config::get('security.session_lifetime', 7200);
        $lastActivity = (int) ($user['last_activity'] ?? 0);

        if ($lifetime > 0 && (time() - $lastActivity) > $lifetime) {
            self::logout();

            return false;
        }

        $_SESSION[self::SESSION_KEY]['last_activity'] = time();

        return true;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return isset($user['id']) ? (int) $user['id'] : null;
    }

    public static function role(): string
    {
        $user = self::user();

        return self::normalizeRole((string) ($user['role'] ?? 'lecteur'));
    }

    public static function hasRole(string $role): bool
    {
        if (!self::check()) {
            return false;
        }

        return (self::LEVELS[self::role()] ?? 0) >= (self::LEVELS[self::normalizeRole($role)] ?? PHP_INT_MAX);
    }

    public static function canEdit(): bool
    {
        return self::hasRole('editeur');
    }

    public static function attempt(string $nom, string $password): bool
    {
        $nom = trim($nom);
        if ($nom === '' || $password === '') {
            return false;
        }

        $model = new Utilisateur();
        $user = $model->findByNom($nom);

        if ($user === null) {
            password_verify($password, '$2y$12$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');

            return false;
        }

        $stored = (string) $user->password;
        $info = password_get_info($stored);
        $isHash = ($info['algoName'] ?? 'unknown') !== 'unknown';
        $valid = $isHash
            ? password_verify($password, $stored)
            : hash_equals($stored, $password);

        if (!$valid) {
            return false;
        }

        if (!$isHash || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            $model->updatePassword((int) $user->id, password_hash($password, PASSWORD_DEFAULT));
        }

        self::login((int) $user->id, (string) $user->nom, self::normalizeRole((string) $user->role));

        return true;
    }

    public static function login(int $id, string $nom, string $role): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        Csrf::rotate();
        $_SESSION[self::SESSION_KEY] = [
            'id' => $id,
            'nom' => $nom,
            'role' => self::normalizeRole($role),
            'login_at' => time(),
            'last_activity' => time(),
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        Csrf::rotate();
    }

    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        if (self::wantsJson()) {
            Response::json([
                'status' => 0,
                'message' => 'Authentification requise.',
            ], 401);
        }

        flash_set('error', 'Vous devez vous connecter pour accéder à cette page.');
        Response::redirect('?p=login');
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();

        if (!self::hasRole($role)) {
            if (self::wantsJson()) {
                Response::json([
                    'status' => 0,
                    'message' => 'Action non autorisée pour votre rôle.',
                ], 403);
            }

            Response::abort(403, 'Votre rôle ne permet pas d\'effectuer cette action.');
        }
    }

    private static function wantsJson(): bool
    {
        $path = $_GET['p'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return (is_string($path) && str_starts_with($path, 'api/'))
            || (is_string($accept) && str_contains($accept, 'application/json'));
    }

    public static function redirectIfAuthenticated(): void
    {
        if (self::check()) {
            Response::redirect('?p=personne');
        }
    }

    public static function normalizeRole(string $role): string
    {
        return match (strtolower(trim($role))) {
            'admin', 'administrateur' => 'admin',
            'editeur', 'éditeur', 'editor', 'user', 'utilisateur' => 'editeur',
            default => 'lecteur',
        };
    }
}
