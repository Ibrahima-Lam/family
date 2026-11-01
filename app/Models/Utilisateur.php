<?php

namespace App\Models;

use PDO;
use stdClass;

class Utilisateur extends Modele
{
    public function findByNom(string $nom): ?stdClass
    {
        $user = $this->fetchOne(
            'SELECT id, nom, password, role FROM user WHERE nom = :nom LIMIT 1',
            ['nom' => $nom],
            PDO::FETCH_OBJ
        );

        return $user ?: null;
    }

    public function findById(int $id): ?stdClass
    {
        $user = $this->fetchOne(
            'SELECT id, nom, password, role FROM user WHERE id = :id LIMIT 1',
            ['id' => $id],
            PDO::FETCH_OBJ
        );

        return $user ?: null;
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->prepare('UPDATE user SET password = :password WHERE id = :id');

        return $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public function updateRole(int $id, string $role): bool
    {
        $stmt = $this->prepare('UPDATE user SET role = :role WHERE id = :id');

        return $stmt->execute(['role' => $role, 'id' => $id]);
    }
}
