<?php

namespace App\Models;

use PDO;
use stdClass;

class Personne extends Modele
{

    public function findAll(): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne,p.surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) ", [], PDO::FETCH_OBJ);
    }
    public function findAllByGeneration(String $idg): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne,p.surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where g.idGeneration=:idg ", ['idg' => $idg], PDO::FETCH_OBJ);
    }
    public function findOne($id): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne,p.surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where p.idPersonne=:id ", ['id' => $id], PDO::FETCH_OBJ);
    }
    public function findLastOne(): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne,p.surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) order by p.idPersonne desc limit 1", [], PDO::FETCH_OBJ);
    }

    public function findAllIdandNom(bool $desc = false): false|array
    {
        $req = "SELECT idPersonne,nomPersonne,surnom FROM personne";
        if ($desc) {
            $req .= " order by idPersonne desc";
        }
        return $this->fetchAll($req, [], PDO::FETCH_ASSOC);
    }
    public function findAllIdandNomByGeneration(string $idg, bool $desc = false): false|array
    {
        $req = "SELECT idPersonne,nomPersonne,surnom FROM personne where generation=:idg";
        if ($desc) {
            $req .= " order by idPersonne desc";
        }
        return $this->fetchAll($req, ['idg' => $idg], PDO::FETCH_ASSOC);
    }

    public function findParent(string $id): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne,p.surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,p.generation as generation";
        return $this->fetchOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where p.idPersonne=:id ", ['id' => $id], PDO::FETCH_OBJ);
    }
    public function findSiblings(string $idParent): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne,p.surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,p.generation as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where e.idParent=:idParent ", ['idParent' => $idParent], PDO::FETCH_OBJ);
    }
    public function findChildren(string $idPersonne): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne,p.surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,p.generation as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where e.idParent=:idPersonne ", ['idPersonne' => $idPersonne], PDO::FETCH_OBJ);
    }
    public function getChildren(string $id): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom,p.surnom as surnom,nomGeneration,p.sexe as sexe,d.nomPersonne as parent,d.surnom as parentSurnom,d.idPersonne as idParent,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where d.idPersonne=:id ";
        return $this->fetchAll($req, ['id' => $id], PDO::FETCH_ASSOC);
    }

    public function getAllByGeneration(string $id): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom,p.surnom as surnom,nomGeneration,p.sexe as sexe,d.nomPersonne as parent,d.surnom as parentSurnom,d.idPersonne as idParent,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where g.idGeneration=:id ";
        return $this->fetchAll($req, ['id' => $id], PDO::FETCH_ASSOC);
    }

    public function getAll(): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom,p.surnom as surnom,nomGeneration,p.sexe,d.nomPersonne as parent,d.surnom as parentSurnom,e.idParent as idParent,p.generation as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) ", [], PDO::FETCH_ASSOC);
    }

    public function findElementGeneration(String $id): int
    {
        $data = $this->fetchOne("select generation from personne where idPersonne=:id", ['id' => $id], PDO::FETCH_OBJ);
        return (int) ($data->generation ?? 0);
    }

    public function findLastId(): string
    {
        $data = $this->fetchOne("SELECT idPersonne FROM personne ORDER BY idPersonne DESC LIMIT 1");
        return (string) ($data->idPersonne ?? '');
    }

    public function addPersonne(string $nom, string $sexe, ?string $parent, ?string $surnom = null): int|false
    {
        return $this->transactional(function () use ($nom, $sexe, $parent, $surnom): int {
            $generation = $parent !== null ? $this->findElementGeneration($parent) + 1 : 1;
            $this->ensureGeneration($generation);

            $stmt = $this->prepare(
                'INSERT INTO personne (nomPersonne, surnom, generation, sexe) VALUES (:nomPersonne, :surnom, :generation, :sexe)'
            );
            $stmt->execute([
                'nomPersonne' => $nom,
                'surnom' => $surnom,
                'generation' => $generation,
                'sexe' => $sexe,
            ]);

            $id = $this->lastInsertId();

            if ($parent !== null) {
                $stmt = $this->prepare(
                    'INSERT INTO etre (idPersonne, idParent) VALUES (:idPersonne, :idParent)'
                );
                $stmt->execute(['idPersonne' => $id, 'idParent' => $parent]);
            }

            return $id;
        });
    }

    public function wouldCreateCycle(int $id, ?string $parent): bool
    {
        return $parent !== null && $this->isDescendant($id, (int) $parent, []);
    }

    public function editPersonne(int $id, string $nom, string $sexe, ?string $parent, ?string $surnom = null): bool
    {
        return $this->transactional(function () use ($id, $nom, $sexe, $parent, $surnom): bool {
            if ($parent !== null && $this->isDescendant($id, (int) $parent, [])) {
                return false;
            }

            $generation = $parent !== null ? $this->findElementGeneration($parent) + 1 : 1;
            $this->ensureGeneration($generation);

            $stmt = $this->prepare(
                'UPDATE personne SET nomPersonne = :nomPersonne, surnom = :surnom, generation = :generation, sexe = :sexe '
                . 'WHERE idPersonne = :idPersonne'
            );
            $stmt->execute([
                'nomPersonne' => $nom,
                'surnom' => $surnom,
                'generation' => $generation,
                'sexe' => $sexe,
                'idPersonne' => $id,
            ]);

            $stmt = $this->prepare('DELETE FROM etre WHERE idPersonne = :idPersonne');
            $stmt->execute(['idPersonne' => $id]);

            if ($parent !== null) {
                $stmt = $this->prepare(
                    'INSERT INTO etre (idPersonne, idParent) VALUES (:idPersonne, :idParent)'
                );
                $stmt->execute(['idPersonne' => $id, 'idParent' => $parent]);
            }

            $visited = [];
            $this->refreshDescendantGenerations($id, $generation, $visited);

            return true;
        });
    }

    public function delete(string $id): bool
    {
        return $this->transactional(function () use ($id): bool {
            $stmt = $this->prepare('DELETE FROM posseder WHERE idPersonne = :id');
            $stmt->execute(['id' => $id]);

            $stmt = $this->prepare('DELETE FROM etre WHERE idPersonne = :id OR idParent = :id');
            $stmt->execute(['id' => $id]);

            $stmt = $this->prepare('DELETE FROM personne WHERE idPersonne = :id');
            $stmt->execute(['id' => $id]);

            return $this->rowCount() > 0;
        });
    }

    private function ensureGeneration(int $generation): void
    {
        if ($generation <= 0) {
            return;
        }

        $stmt = $this->prepare(
            'INSERT OR IGNORE INTO generation (idGeneration, nomGeneration) VALUES (:id, :nom)'
        );
        $stmt->execute(['id' => $generation, 'nom' => 'G' . $generation]);
    }

    /** @param array<int, bool> $visited */
    private function isDescendant(int $ancestorId, int $candidateId, array $visited): bool
    {
        if ($ancestorId === $candidateId) {
            return true;
        }
        if (isset($visited[$ancestorId])) {
            return false;
        }
        $visited[$ancestorId] = true;

        foreach ($this->findChildren((string) $ancestorId) as $child) {
            if ((int) $child->idPersonne === $candidateId) {
                return true;
            }
            if ($this->isDescendant((int) $child->idPersonne, $candidateId, $visited)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, bool> $visited */
    private function refreshDescendantGenerations(int $parentId, int $parentGeneration, array &$visited): void
    {
        if (isset($visited[$parentId])) {
            return;
        }
        $visited[$parentId] = true;

        $children = $this->findChildren((string) $parentId);
        foreach ($children as $child) {
            $childId = (int) $child->idPersonne;
            if (isset($visited[$childId])) {
                continue;
            }

            $generation = $parentGeneration + 1;
            $this->ensureGeneration($generation);

            $stmt = $this->prepare('UPDATE personne SET generation = :generation WHERE idPersonne = :id');
            $stmt->execute(['generation' => $generation, 'id' => $childId]);

            $this->refreshDescendantGenerations($childId, $generation, $visited);
        }
    }
}
