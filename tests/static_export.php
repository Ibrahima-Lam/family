<?php

declare(strict_types=1);

use App\Support\StaticSiteGenerator;
use Core\Service\Database;
use Core\Service\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

$tempDb = sys_get_temp_dir() . '/famille-static-db-' . bin2hex(random_bytes(4)) . '.db';
$tempDir = sys_get_temp_dir() . '/famille-static-site-' . bin2hex(random_bytes(4));
copy(dirname(__DIR__) . '/database/famille.db', $tempDb);
putenv('DB_PATH=' . $tempDb);

(new Migrator(Database::connection(), dirname(__DIR__) . '/database/migrations'))->run();

$failures = 0;

function check(bool $condition, string $message): void
{
    global $failures;

    if (!$condition) {
        fwrite(STDERR, "ÉCHEC: $message\n");
        $failures++;
        return;
    }

    echo "OK: $message\n";
}

$generator = new StaticSiteGenerator($tempDir);
$files = $generator->generate();

check(count($files) > 5, 'Le site statique contient plusieurs pages');
check(is_file($tempDir . '/index.html'), 'La page d’accueil est générée');
check(is_file($tempDir . '/liste.html'), 'La liste est générée');
check(is_file($tempDir . '/arbre.html'), 'L’arbre est généré');
check(is_file($tempDir . '/livre.html'), 'Le livre est généré');
check(is_file($tempDir . '/descendances.html'), 'La page des descendances est générée');
check(is_file($tempDir . '/personne/45.html'), 'Les fiches personnes sont générées');
check(is_file($tempDir . '/assets/css/modern.css'), 'La feuille de style est copiée');
check(is_file($tempDir . '/assets/js/static-search.js'), 'La recherche statique est copiée');

$index = (string) file_get_contents($tempDir . '/index.html');
$tree = (string) file_get_contents($tempDir . '/arbre.html');
$person = (string) file_get_contents($tempDir . '/personne/45.html');

check(!str_contains($index, '?p='), 'Aucun lien dynamique dans l’accueil');
check(!str_contains($tree, '?p='), 'Aucun lien dynamique dans l’arbre');
check(!str_contains($person, '?p='), 'Aucun lien dynamique dans une fiche');
check(str_contains($tree, 'personne/46.html'), 'L’arbre pointe vers les fiches HTML');
check(str_contains($index, 'Descendances à vérifier'), 'L’accueil signale les descendances à vérifier');
check(str_contains($person, 'Descendance'), 'La fiche affiche le statut de descendance');

$items = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($tempDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($items as $item) {
    $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
}
rmdir($tempDir);

@unlink($tempDb);
@unlink($tempDb . '-wal');
@unlink($tempDb . '-shm');

if ($failures > 0) {
    fwrite(STDERR, "\n$failures test(s) en échec.\n");
    exit(1);
}

echo "\nTous les tests d’export statique sont passés.\n";
