<?php
class Connexion
{
    private $pdo;
    public function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $dbPath = $config['path'];
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo = $pdo;
    }

    public function fetchOne(string $sql, array $params = [], int $mode = PDO::FETCH_ASSOC): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch($mode);
    }

    public function fetchAll(string $sql, array $params = [], int $mode = PDO::FETCH_ASSOC): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($mode);
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
