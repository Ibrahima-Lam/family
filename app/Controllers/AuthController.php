<?php

namespace App\Controllers;

use App\Security\Auth;
use Core\Controllers\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Core\Security\RateLimiter;
use Core\Support\Config;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->render('auth/login', [
            'error' => flash_get('error'),
            'nom' => '',
        ]);
    }

    public function login(): void
    {
        $request = new Request();
        $nom = $request->string('nom');
        $password = (string) ($_POST['password'] ?? '');
        $maxAttempts = (int) Config::get('security.login_max_attempts', 5);
        $decay = (int) Config::get('security.login_decay', 900);
        $key = 'login:' . $request->ip() . ':' . strtolower($nom);

        if ($nom === '' || $password === '') {
            flash_set('error', 'Le nom et le mot de passe sont obligatoires.');

            Response::redirect('?p=login');
        }

        if (RateLimiter::tooManyAttempts($key, $maxAttempts, $decay)) {
            $retry = RateLimiter::availableIn($key, $maxAttempts, $decay);
            flash_set('error', 'Trop de tentatives. Réessayez dans ' . $retry . ' secondes.');

            Response::redirect('?p=login');
        }

        if (!Auth::attempt($nom, $password)) {
            RateLimiter::hit($key, $decay);
            flash_set('error', 'Identifiants incorrects.');

            Response::redirect('?p=login');
        }

        RateLimiter::clear($key);
        flash_set('success', 'Connexion réussie.');

        Response::redirect('?p=personne');
    }

    public function logout(): void
    {
        Auth::logout();
        flash_set('success', 'Vous êtes déconnecté.');

        Response::redirect('?p=app/index');
    }
}
