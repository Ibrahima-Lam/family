<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$classes = [
    App\Controllers\ApiController::class,
    App\Controllers\AppController::class,
    App\Controllers\AuthController::class,
    App\Controllers\PersonneController::class,
    App\Models\Generation::class,
    App\Models\Modele::class,
    App\Models\Personne::class,
    App\Models\Utilisateur::class,
    App\Security\Auth::class,
    Core\Controllers\Controller::class,
    Core\ErrorHandler::class,
    Core\Exceptions\HttpException::class,
    Core\Http\Request::class,
    Core\Http\Response::class,
    Core\Router::class,
    Core\Security\Csrf::class,
    Core\Security\RateLimiter::class,
    Core\Service\Database::class,
    Core\Service\Migrator::class,
    Core\Support\Config::class,
    Src\Altoperson::class,
    Src\Arbre::class,
    Src\Html::class,
    Src\Lien::class,
    Src\Link::class,
    Src\Person::class,
    Src\Personnage::class,
];

$failed = 0;
foreach ($classes as $class) {
    if (!class_exists($class)) {
        fwrite(STDERR, "Classe introuvable: $class\n");
        $failed++;
    }
}

if ($failed > 0) {
    exit(1);
}

echo 'OK: ' . count($classes) . " classes autoloadées\n";
