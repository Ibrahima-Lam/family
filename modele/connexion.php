<?php
class Connexion
{
    private $pdo;
    public function __construct()
    {
        $pdo = new PDO("sqlite:../modele/famille.db");
        $this->pdo = $pdo;
    }
    public function firstId(): mixed
    {
        $res = $this->pdo->query("select idPersonne from personne ")->fetch(2)["idPersonne"];
        return $res ?? null;
    }

    public function select(string $req, int|string $type = 2, bool $one = false): mixed
    {
        $query = $this->pdo->query($req);
        if (is_string($type)) {
            $query->setFetchMode(PDO::FETCH_CLASS, $type);
        } else    $query->setFetchMode($type);

        $res = $one ? $query->fetch() : $query->fetchAll();
        return $res ?? null;
    }
    public function selectAll(string $req, int|string $type = 2): array|false
    {
        $query = $this->pdo->query($req);
        if (is_string($type)) {
            $query->setFetchMode(PDO::FETCH_CLASS, $type);
        } else    $query->setFetchMode($type);

        $res = $query->fetchAll();
        return $res ?? null;
    }
}
