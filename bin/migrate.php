<?php

declare(strict_types=1);

use Core\Service\Database;
use Core\Service\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

$pdo = Database::connection();
$migrator = new Migrator($pdo, dirname(__DIR__) . '/database/migrations');
$executed = $migrator->run();

if ($executed === []) {
    echo "Aucune migration à exécuter.\n";
    exit(0);
}

foreach ($executed as $migration) {
    echo "Migration exécutée : $migration\n";
}
