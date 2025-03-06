<?php

namespace App\Modele;

use PDO;
use stdClass;

class Personne extends Modele
{

    public function findAll(): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        $data = $this->selectAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) ", PDO::FETCH_OBJ);
        return $data;
    }
    public function findAllByGeneration(String $idg): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        $data = $this->selectAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where g.idGeneration='$idg' ", PDO::FETCH_OBJ);
        return $data;
    }
    public function findOne($id): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        $data = $this->selectOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where p.idPersonne='$id' ", PDO::FETCH_OBJ);
        return $data;
    }
    public function findLastOne(): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,g.idGeneration as generation";
        $data = $this->selectOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) order by p.idPersonne desc limit 1", PDO::FETCH_OBJ);
        return $data;
    }

    public function findAllIdandNom(bool $desc = false): false|array
    {
        $req = "SELECT idPersonne,nomPersonne FROM personne";
        if ($desc) {
            $req .= " order by idPersonne desc";
        }
        $data = $this->selectAll($req, PDO::FETCH_ASSOC);
        return $data;
    }
    public function findAllIdandNomByGeneration(string $idg, bool $desc = false): false|array
    {
        $req = "SELECT idPersonne,nomPersonne FROM personne where generation='$idg'";
        if ($desc) {
            $req .= " order by idPersonne desc";
        }
        $data = $this->selectAll($req, PDO::FETCH_ASSOC);
        return $data;
    }

    public function findParent(string $id): false|stdClass
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
        $data = $this->selectOne("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where p.idPersonne='$id' ", PDO::FETCH_OBJ);
        return $data;
    }
    public function findSiblings(string $idParent): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
        $data = $this->selectAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where e.idParent='$idParent' ", PDO::FETCH_OBJ);
        return $data;
    }
    public function findChildren(string $idPersonne): false|array
    {
        $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
        $data = $this->selectAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) where e.idParent='$idPersonne' ", PDO::FETCH_OBJ);
        return $data;
    }
    public function getChildren(string $id): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe as sexe,d.nomPersonne as parent, d.idPersonne as idParent ,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where d.idPersonne='$id' ";
        $children = $this->selectAll($req, PDO::FETCH_ASSOC);
        return $children;
    }

    public function getAllByGeneration(string $id): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe as sexe,d.nomPersonne as parent, d.idPersonne as idParent ,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where g.idGeneration='$id' ";
        $children = $this->selectAll($req, PDO::FETCH_ASSOC);
        return $children;
    }

    public function getAll(): false|array
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent,p.generation as generation";
        $data = $this->selectAll("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
            left join personne d on(e.idParent=d.idPersonne) ", PDO::FETCH_ASSOC);
        return $data;
    }

    public function findElementGeneration(String $id): int
    {
        $data = $this->selectOne("select generation from personne where idPersonne='$id'");
        return $data->generation;
    }

    public function findLastId(): string
    {
        $data = $this->selectOne("SELECT idPersonne FROM personne ORDER BY idPersonne DESC LIMIT 1");
        return $data->idPersonne;
    }

    public function addPersonne($nom, string $generation, string $sexe, string $parent): bool
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
    public function editPersonne($id, $nom, string $generation, string $sexe, string $parent): bool
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
        $sql = "DELETE FROM personne WHERE idPersonne=:id";
        $stmt = $this->prepare($sql);
        $res = $stmt->execute(["id" => $id]);
        return $res;
    }
}
