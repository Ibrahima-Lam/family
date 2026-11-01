<?php

return static function (\PDO $pdo): void {
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_personne_generation ON personne(generation)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_etre_parent ON etre(idParent)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_etre_personne ON etre(idPersonne)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_user_nom ON user(nom)');

    $rows = $pdo->query('SELECT id, password, role FROM user')->fetchAll(PDO::FETCH_ASSOC);
    $update = $pdo->prepare('UPDATE user SET password = :password, role = :role WHERE id = :id');

    foreach ($rows as $row) {
        $stored = (string) $row['password'];
        $info = password_get_info($stored);
        $isHash = ($info['algoName'] ?? 'unknown') !== 'unknown';

        $hash = $isHash
            ? $stored
            : password_hash($stored, PASSWORD_DEFAULT);

        $role = match (strtolower(trim((string) $row['role']))) {
            'admin', 'administrateur' => 'admin',
            'editeur', 'éditeur', 'editor', 'user', 'utilisateur' => 'editeur',
            default => 'lecteur',
        };

        $update->execute([
            'password' => $hash,
            'role' => $role,
            'id' => $row['id'],
        ]);
    }
};
