<?php

use App\Controllers\ApiController;

return [
    ['GET', 'api/personnes', [ApiController::class, 'personnes']],
    ['GET', 'api/personne/{id}', [ApiController::class, 'personne']],
    ['GET', 'api/names', [ApiController::class, 'names']],
    ['GET', 'api/generations', [ApiController::class, 'generations']],
    ['GET', 'api/options', [ApiController::class, 'options']],
    ['GET', 'api/options/{idg}', [ApiController::class, 'options']],
    ['GET', 'api/arbre', [ApiController::class, 'arbre']],
    ['GET', 'api/arbre/{id}', [ApiController::class, 'arbre']],

    ['POST', 'api/add', [ApiController::class, 'add'], ['auth', 'role:editeur', 'csrf']],
    ['POST', 'api/edit', [ApiController::class, 'edit'], ['auth', 'role:editeur', 'csrf']],
    ['POST', 'api/delete', [ApiController::class, 'delete'], ['auth', 'role:editeur', 'csrf']],
    ['POST', 'api/delete/{id}', [ApiController::class, 'delete'], ['auth', 'role:editeur', 'csrf']],
];
