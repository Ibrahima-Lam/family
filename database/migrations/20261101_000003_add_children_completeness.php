<?php

return static function (\PDO $pdo): void {
    $columns = $pdo->query('PRAGMA table_info(personne)')->fetchAll(\PDO::FETCH_COLUMN, 1);

    if (!in_array('enfants_statut', $columns, true)) {
        $pdo->exec(
            "ALTER TABLE personne ADD COLUMN enfants_statut TEXT NOT NULL DEFAULT 'inconnu'"
        );
    }

    if (!in_array('enfants_manquants', $columns, true)) {
        $pdo->exec(
            'ALTER TABLE personne ADD COLUMN enfants_manquants INTEGER NOT NULL DEFAULT 0'
        );
    }

    if (!in_array('enfants_note', $columns, true)) {
        $pdo->exec('ALTER TABLE personne ADD COLUMN enfants_note TEXT');
    }

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_personne_enfants_statut ON personne(enfants_statut)');

    $pdo->exec(
        "UPDATE personne SET enfants_statut = 'inconnu' "
        . "WHERE enfants_statut IS NULL OR enfants_statut NOT IN ('inconnu', 'aucun', 'complet', 'incomplet')"
    );
    $pdo->exec('UPDATE personne SET enfants_manquants = 0 WHERE enfants_manquants IS NULL OR enfants_manquants < 0');
    $pdo->exec(
        "UPDATE personne SET enfants_manquants = 0 "
        . "WHERE enfants_statut IN ('inconnu', 'aucun', 'complet') AND enfants_manquants > 0"
    );
    $pdo->exec(
        "UPDATE personne SET enfants_manquants = 0 "
        . "WHERE enfants_statut = 'incomplet' AND enfants_manquants < 0"
    );
};
