<?php

namespace Core\Service;

use \PDO;

class Database
{
    private $pdo;
    private $prepare;

    public function __construct()
    {
        // $dns="mysql:dbname=$db;host=localhost";
        $dbPath = getenv('DB_PATH') ?: dirname(__DIR__, 2) . '/DB/famille.db';
        $dns = 'sqlite:' . $dbPath;
        $this->pdo = new PDO($dns);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        //$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    public function select(string $req, $mode = PDO::FETCH_ASSOC, $one = false): mixed
    {

        $res = $this->pdo->query($req);
        if ($one) {
            if (is_string($mode)) {
                $res->setFetchMode(PDO::FETCH_CLASS, $mode);
                $result = $res->fetch();
            } else {
                $res->setFetchMode($mode);
                $result = $res->fetch();
            }
            return $result;
        }

        if (is_string($mode)) {
            $res->setFetchMode(PDO::FETCH_CLASS, $mode);
            $result = $res->fetchAll();
        } else {
            $res->setFetchMode($mode);
            $result = $res->fetchAll();
        }
        return $result;
    }


    public function selectOne(string $req, $mode = PDO::FETCH_OBJ): mixed
    {
        return $this->select($req, $mode, true);
    }


    public function selectAll(string $req, $mode = PDO::FETCH_ASSOC): mixed
    {
        return  $this->select($req, $mode, false);
    }


    public function fetchOne(string $sql, array $params = [], $mode = PDO::FETCH_OBJ): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch($mode);
    }


    public function fetchAll(string $sql, array $params = [], $mode = PDO::FETCH_ASSOC): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($mode);
    }


    public function exec(string $req): bool
    {
        return $this->pdo->exec($req);
    }


    public function prepare(string $req): self
    {
        $this->prepare = $this->pdo->prepare($req);
        return $this;
    }


    public function execute(?array $params = null): bool
    {
        return   $this->prepare->execute($params);
    }


    public function getResult($mode = PDO::FETCH_ASSOC, $one = false): mixed
    {
        if (is_string($mode)) {
            $this->prepare->setFetchMode(PDO::FETCH_CLASS, $mode);
        } else $this->prepare->setFetchMode($mode);

        if ($one) {
            return $this->prepare->fetch();
        }
        return $this->prepare->fetchAll();
    }


    public function transaction(string ...$reqs): bool
    {
        $size = sizeof($reqs);

        $this->pdo->beginTransaction();

        $check = 0;
        foreach ($reqs as $item) {
            $check += $this->pdo->exec($item);
        }

        if ($check === $size) {
            $this->pdo->commit();
            return true;
        } else $this->pdo->rollBack();

        return false;
    }


    public function lastInsertId(): int
    {
        return  $this->pdo->lastInsertId();
    }
}
