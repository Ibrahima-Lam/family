<?php

namespace App\Modele;

use PDO;

class Generation extends Modele
{
    public function findAll(): false|array
    {
        $prop = "g.idGeneration,g.nomGeneration";
        $data = $this->selectAll("SELECT $prop FROM generation g", PDO::FETCH_ASSOC);
        return $data;
    }
    public function checkGeneration(int $id): bool
    {
        $data = $this->select("SELECT * FROM generation WHERE idGeneration > '$id'",  PDO::FETCH_ASSOC);
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
