<?php

declare(strict_types=1);

use App\Support\StaticSiteGenerator;

require dirname(__DIR__) . '/vendor/autoload.php';

$options = getopt('', ['output::', 'out::', 'root::', 'db::', 'clean', 'help']);

if (isset($options['help'])) {
    echo "Usage : php bin/export-static.php [options]\n\n";
    echo "  --output=DIR  Dossier de sortie (défaut : storage/static)\n";
    echo "  --root=ID     Identifiant de la racine de l’arbre\n";
    echo "  --db=PATH     Chemin de la base SQLite (défaut : config/database.php)\n";
    echo "  --clean       Vider le dossier de sortie avant génération\n";
    echo "  --help        Afficher cette aide\n";
    exit(0);
}

$output = (string) ($options['output'] ?? $options['out'] ?? dirname(__DIR__) . '/storage/static');
$root = isset($options['root']) ? (string) $options['root'] : null;

if (isset($options['db']) && $options['db'] !== '') {
    putenv('DB_PATH=' . (string) $options['db']);
    $_ENV['DB_PATH'] = (string) $options['db'];
}

if (isset($options['clean']) && is_dir($output)) {
    $real = realpath($output);
    $protected = [realpath('/'), realpath(dirname(__DIR__)), realpath(dirname(__DIR__) . '/storage')];

    if ($real === false || in_array($real, $protected, true)) {
        fwrite(STDERR, "Refus de vider un dossier protégé : $output\n");
        exit(1);
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
}

try {
    $generator = new StaticSiteGenerator($output, $root);
    $files = $generator->generate();
} catch (Throwable $exception) {
    fwrite(STDERR, "Échec de l’export statique : " . $exception->getMessage() . "\n");
    exit(1);
}

echo "Site statique généré dans : $output\n";
echo count($files) . " page(s) écrite(s), assets copiés.\n";
foreach (array_slice($files, 0, 8) as $file) {
    echo '  - ' . $file . "\n";
}
if (count($files) > 8) {
    echo '  … et ' . (count($files) - 8) . " autre(s).\n";
}
