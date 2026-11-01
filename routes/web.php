<?php

use App\Controllers\AppController;
use App\Controllers\AuthController;
use App\Controllers\PersonneController;

return [
    ['GET', '', [AppController::class, 'index']],
    ['GET', 'app/index', [AppController::class, 'index']],

    ['GET', 'login', [AuthController::class, 'showLogin'], ['guest']],
    ['POST', 'login', [AuthController::class, 'login'], ['guest', 'csrf']],
    ['POST', 'logout', [AuthController::class, 'logout'], ['auth', 'csrf']],

    ['GET', 'personne', [PersonneController::class, 'index']],
    ['GET', 'personne/details', [PersonneController::class, 'details']],
    ['GET', 'personne/details/{id}', [PersonneController::class, 'details']],
    ['GET', 'personne/arbre', [PersonneController::class, 'arbre']],
    ['GET', 'personne/arbre/{id}', [PersonneController::class, 'arbre']],
    ['GET', 'personne/lien', [PersonneController::class, 'lien']],
    ['POST', 'personne/lien', [PersonneController::class, 'lien'], ['csrf']],
    ['GET', 'personne/livre', [PersonneController::class, 'livre']],
    ['GET', 'personne/incomplets', [PersonneController::class, 'incomplets']],
    ['POST', 'personne/descendance', [PersonneController::class, 'descendance'], ['auth', 'role:editeur', 'csrf']],

    ['GET', 'personne/ajout', [PersonneController::class, 'ajout'], ['auth', 'role:editeur']],
    ['GET', 'personne/edit', [PersonneController::class, 'edit'], ['auth', 'role:editeur']],
];
