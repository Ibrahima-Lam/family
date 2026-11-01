<?php

declare(strict_types=1);

use App\Models\Personne;
use App\Support\Descendance;

require dirname(__DIR__) . '/vendor/autoload.php';

$options = getopt('', ['set::', 'id::', 'all', 'from-children', 'help']);

if (isset($options['help'])) {
    echo "Usage : php bin/manage-descendance.php [options]\n\n";
    echo "  --id=ID           Gérer une seule personne\n";
    echo "  --set=STATUT      Définir le statut (inconnu|aucun|complet|incomplet)\n";
    echo "  --all             Appliquer à toutes les personnes\n";
    echo "  --from-children   Déduire le statut depuis le nombre d'enfants\n";
    echo "  --help            Afficher cette aide\n";
    exit(0);
}

$model = new Personne();

if (isset($options['from-children']) && isset($options['all'])) {
    $people = $model->findAll();
    $count = 0;
    foreach ($people as $person) {
        $id = (int) ($person->idPersonne ?? 0);
        if ($id <= 0) continue;
        $childrenCount = (int) ($person->enfantsCount ?? 0);
        $current = Descendance::normalize($person->enfantsStatut ?? null);
        
        // Ne pas écraser un statut déjà défini manuellement (complet/incomplet avec info)
        if ($current === Descendance::COMPLET || $current === Descendance::INCOMPLET) {
            continue;
        }
        
        if ($childrenCount === 0) {
            $model->saveDescendance($id, Descendance::AUCUN, 0, null);
            $count++;
        } elseif ($current === Descendance::INCONNU) {
            // Garder inconnu par défaut, ou passer en incomplet si on veut forcer
            // Pour l'instant on laisse inconnu
        }
    }
    echo "Mis à jour : $count personne(s)\n";
    exit(0);
}

if (isset($options['id']) && isset($options['set'])) {
    $id = (int) $options['id'];
    $statut = Descendance::normalize($options['set']);
    if ($model->saveDescendance($id, $statut, 0, null)) {
        echo "Statut mis à jour pour #$id : " . Descendance::label($statut) . "\n";
    } else {
        echo "Échec pour #$id\n";
        exit(1);
    }
    exit(0);
}

echo "Utilisez --help pour voir les options\n";
