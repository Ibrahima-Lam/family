<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Models\Personne;

$db = new \PDO('sqlite:' . dirname(__DIR__) . '/database/famille.db');
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys = ON');

// Passer TOUT ce qui est 'inconnu' en 'complet'
$stmt = $db->prepare("UPDATE personne SET enfants_statut = 'complet', enfants_manquants = 0 WHERE enfants_statut = 'inconnu'");
$stmt->execute();
echo "Mis à jour: " . $stmt->rowCount() . " personnes (inconnu -> complet)\n";

$model = new Personne();
$stats = $model->countByDescendanceStatut();
foreach ($stats as $k => $v) {
    echo "  $k: $v\n";
}
