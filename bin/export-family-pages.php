<?php

declare(strict_types=1);

use App\Support\InlineFamilyPagesGenerator;

require dirname(__DIR__) . '/vendor/autoload.php';

$options = getopt('', ['output::', 'out::', 'root::', 'db::', 'help']);

if (isset($options['help'])) {
    echo "Usage : php bin/export-family-pages.php [options]\n\n";
    echo "  --output=DIR  Dossier de sortie (défaut : dist)\n";
    echo "  --root=ID     Identifiant de la racine de l’arbre\n";
    echo "  --db=PATH     Chemin de la base SQLite (défaut : config/database.php)\n";
    echo "  --help        Afficher cette aide\n";
    exit(0);
}

$output = (string) ($options['output'] ?? $options['out'] ?? dirname(__DIR__) . '/dist');
$root = isset($options['root']) ? (string) $options['root'] : null;

if (isset($options['db']) && $options['db'] !== '') {
    putenv('DB_PATH=' . (string) $options['db']);
    $_ENV['DB_PATH'] = (string) $options['db'];
}

try {
    $files = (new InlineFamilyPagesGenerator($output, $root))->generate();
} catch (Throwable $exception) {
    fwrite(STDERR, "Échec de l’export des pages familiales : " . $exception->getMessage() . "\n");
    exit(1);
}

echo "Pages autonomes générées dans : $output\n";
foreach ($files as $file) {
    echo '  - ' . $file . "\n";
}
