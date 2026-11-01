<?php

namespace Core\Service;

use PDO;
use Throwable;

class Database
{
    private static ?PDO $connection = null;

    private PDO $pdo;
    private $prepare;

    public function __construct()
    {
        $this->pdo = self::connection();
    }

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $dsn = 'sqlite:' . $config['path'];

        self::$connection = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::$connection->exec('PRAGMA foreign_keys = ON');
        self::$connection->exec('PRAGMA busy_timeout = 5000');
        self::$connection->exec('PRAGMA synchronous = NORMAL');

        try {
            self::$connection->exec('PRAGMA journal_mode = WAL');
        } catch (Throwable) {
            // Le mode WAL n'est pas disponible sur certains systèmes de fichiers.
        }

        return self::$connection;
    }

    public static function pdo(): PDO
    {
        return self::connection();
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
        return $this->select($req, $mode, false);
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
        return $this->pdo->exec($req) !== false;
    }

    public function prepare(string $req): self
    {
        $this->prepare = $this->pdo->prepare($req);

        return $this;
    }

    public function execute(?array $params = null): bool
    {
        return $this->prepare->execute($params);
    }

    public function getResult($mode = PDO::FETCH_ASSOC, $one = false): mixed
    {
        if (is_string($mode)) {
            $this->prepare->setFetchMode(PDO::FETCH_CLASS, $mode);
        } else {
            $this->prepare->setFetchMode($mode);
        }

        if ($one) {
            return $this->prepare->fetch();
        }

        return $this->prepare->fetchAll();
    }

    public function rowCount(): int
    {
        return $this->prepare->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function transactional(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->rollBack();
            }

            throw $exception;
        }
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
        }

        $this->pdo->rollBack();

        return false;
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
