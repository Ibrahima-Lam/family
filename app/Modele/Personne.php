<?php

namespace App\Modele;

use PDO;
use stdClass;

class Personne extends Modele
{

    public function findAll(): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) ", [], PDO::FETCH_OBJ);
    }
    public function findAllByGeneration(String $idg): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where g.idGeneration=:idg ", ['idg' => $idg], PDO::FETCH_OBJ);
    }
    public function findOne($id): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where p.idPersonne=:id ", ['id' => $id], PDO::FETCH_OBJ);
    }
    public function findLastOne(): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        return $this->fetchOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) order by p.idPersonne desc limit 1", [], PDO::FETCH_OBJ);
    }

    public function findAllIdandNom(bool $desc = false): false|array
    {
        $req = "SELECT idPersonne,nomPersonne FROM personne";
        if ($desc) {
            $req .= " order by idPersonne desc";
        }
        return $this->fetchAll($req, [], PDO::FETCH_ASSOC);
    }
    public function findAllIdandNomByGeneration(string $idg, bool $desc = false): false|array
    {
        $req = "SELECT idPersonne,nomPersonne FROM personne where generation=:idg";
        if ($desc) {
            $req .= " order by idPersonne desc";
        }
        return $this->fetchAll($req, ['idg' => $idg], PDO::FETCH_ASSOC);
    }

    public function findParent(string $id): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
        return $this->fetchOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where p.idPersonne=:id ", ['id' => $id], PDO::FETCH_OBJ);
    }
    public function findSiblings(string $idParent): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where e.idParent=:idParent ", ['idParent' => $idParent], PDO::FETCH_OBJ);
    }
    public function findChildren(string $idPersonne): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
        return $this->fetchAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where e.idParent=:idPersonne ", ['idPersonne' => $idPersonne], PDO::FETCH_OBJ);
    }
    public function getChildren(string $id): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe as sexe,d.nomPersonne as parent, d.idPersonne as idParent ,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where d.idPersonne=:id ";
        return $this->fetchAll($req, ['id' => $id], PDO::FETCH_ASSOC);
    }

    public function getAllByGeneration(string $id): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe as sexe,d.nomPersonne as parent, d.idPersonne as idParent ,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where g.idGeneration=:id ";
        return $this->fetchAll($req, ['id' => $id], PDO::FETCH_ASSOC);
    }

    public function getAll(): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
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

    public function addPersonne($nom, string $generation, string $sexe, ?string $parent): bool
    {
        $data = [
            "idPersonne" => null,
            "nomPersonne" => $nom,
            "generation" => $generation,
            "sexe" => $sexe,

        ];
        $sql = "INSERT INTO personne  VALUES (:idPersonne,:nomPersonne,:generation,:sexe)";
        $stmt = $this->prepare($sql);
        $res = $stmt->execute($data);
        if ($res) {
            $id = $this->lastInsertId();

            $sql = "INSERT INTO etre  VALUES (null,:idPersonne,:idParent)";
            $stmt = $this->prepare($sql);
            $res = $stmt->execute(["idPersonne" => $id, "idParent" => $parent]);
            return $res;
        }
        return false;
    }
    public function editPersonne($id, $nom, string $generation, string $sexe, ?string $parent): bool
    {
        $data = [
            "idPersonne" => $id,
            "nomPersonne" => $nom,
            "generation" => $generation,
            "sexe" => $sexe,

        ];
        $sql = "UPDATE personne SET nomPersonne=:nomPersonne,generation=:generation,sexe=:sexe WHERE idPersonne=:idPersonne";
        $stmt = $this->prepare($sql);
        $res = $stmt->execute($data);
        if ($res) {


            $sql = "UPDATE etre SET idParent=:idParent WHERE idPersonne=:idPersonne";
            $stmt = $this->prepare($sql);
            $res = $stmt->execute(["idPersonne" => $id, "idParent" => $parent]);
            return $res;
        }
        return false;
    }
    public function delete(string $id): bool
    {
        $stmt = $this->prepare("DELETE FROM etre WHERE idPersonne=:id");
        $stmt->execute(["id" => $id]);
        $sql = "DELETE FROM personne WHERE idPersonne=:id";
        $stmt = $this->prepare($sql);
        $res = $stmt->execute(["id" => $id]);
        return $res;
    }
}
