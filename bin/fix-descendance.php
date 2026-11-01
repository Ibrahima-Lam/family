<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Models\Personne;

$model = new Personne();
// Use reflection or just direct query via model's db
$db = new \PDO('sqlite:' . dirname(__DIR__) . '/database/famille.db');
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys = ON');

// Pour chaque personne avec statut inconnu
$rows = $db->query("SELECT idPersonne, (SELECT COUNT(*) FROM etre WHERE idParent = personne.idPersonne) as nb FROM personne WHERE enfants_statut = 'inconnu'")->fetchAll(\PDO::FETCH_ASSOC);

$updated = 0;
foreach ($rows as $row) {
    $id = (int)$row['idPersonne'];
    $nb = (int)$row['nb'];
    if ($nb == 0) {
        // Pas d'enfants connus -> aucun
        $stmt = $db->prepare("UPDATE personne SET enfants_statut = 'aucun', enfants_manquants = 0 WHERE idPersonne = ?");
        $stmt->execute([$id]);
        $updated++;
    }
}

echo "Mis à jour: $updated personnes (sans enfants -> 'aucun')\n";

// Afficher stats
$stats = $model->countByDescendanceStatut();
foreach ($stats as $k => $v) {
    echo "  $k: $v\n";
}
