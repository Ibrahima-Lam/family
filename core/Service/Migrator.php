<?php

namespace Core\Service;

use PDO;
use RuntimeException;
use Throwable;

final class Migrator
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $directory
    ) {
    }

    /** @return array<int, string> */
    public function run(): array
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations ('
            . 'migration TEXT PRIMARY KEY, applied_at TEXT NOT NULL)'
        );

        $applied = $this->pdo
            ->query('SELECT migration FROM schema_migrations')
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $files = glob($this->directory . '/*.php') ?: [];
        sort($files);

        $executed = [];

        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $applied, true)) {
                continue;
            }

            $callback = require $file;
            if (!is_callable($callback)) {
                throw new RuntimeException('Migration invalide: ' . $name);
            }

            $this->pdo->beginTransaction();

            try {
                $callback($this->pdo);

                $stmt = $this->pdo->prepare(
                    'INSERT INTO schema_migrations (migration, applied_at) VALUES (:migration, :applied_at)'
                );
                $stmt->execute(['migration' => $name, 'applied_at' => date('c')]);

                $this->pdo->commit();
                $executed[] = $name;
            } catch (Throwable $exception) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }

                throw $exception;
            }
        }

        return $executed;
    }
}
