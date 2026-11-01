<?php

return static function (\PDO $pdo): void {
    $columns = $pdo->query('PRAGMA table_info(personne)')->fetchAll(\PDO::FETCH_COLUMN, 1);

    if (!in_array('surnom', $columns, true)) {
        $pdo->exec('ALTER TABLE personne ADD COLUMN surnom TEXT');
    }

    $rows = $pdo->query('SELECT idPersonne, nomPersonne FROM personne')->fetchAll(\PDO::FETCH_ASSOC);
    $update = $pdo->prepare(
        'UPDATE personne SET nomPersonne = :nomPersonne, surnom = :surnom WHERE idPersonne = :idPersonne'
    );
    $normalize = static fn (string $value): string => trim((string) preg_replace('/\s+/u', ' ', $value));

    foreach ($rows as $row) {
        $name = $normalize((string) ($row['nomPersonne'] ?? ''));
        $parts = preg_split('~[/\\\\]~u', $name, 2);

        if (!is_array($parts) || count($parts) !== 2) {
            continue;
        }

        $main = $normalize($parts[0]);
        $surname = $normalize($parts[1]);

        if ($main === '' || $surname === '') {
            continue;
        }

        $update->execute([
            'nomPersonne' => $main,
            'surnom' => $surname,
            'idPersonne' => $row['idPersonne'],
        ]);
    }
};
