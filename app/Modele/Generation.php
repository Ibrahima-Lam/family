<?php

namespace App\Modele;

use PDO;

class Generation extends Modele
{
    public function findAll(): false|array
    {
        $prop = "g.idGeneration,g.nomGeneration";
        return $this->fetchAll("SELECT $prop FROM generation g", [], PDO::FETCH_ASSOC);
    }
    public function checkGeneration(int $id): bool
    {
        $data = $this->fetchAll("SELECT * FROM generation WHERE idGeneration > :id", ['id' => $id], PDO::FETCH_ASSOC);
        return !empty($data);
    }
    public function addGeneration(int $id): bool
    {
        $data = [
            "idGeneration" => $id,
            "nomGeneration" => "G$id"
        ];
        $sql = "INSERT INTO generation  VALUES (:idGeneration, :nomGeneration)";
        $stmt = $this->prepare($sql);
        $res = $stmt->execute($data);
        return $res;
    }
}
