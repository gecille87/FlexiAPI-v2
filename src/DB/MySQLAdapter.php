<?php

namespace FlexiAPI\DB;

use PDO;
use PDOException;

class MySQLAdapter implements DBAdapterInterface
{
    private PDO $pdo;
    private array $config;
    private array $whitelist;

    public function __construct(array $config)
    {
        $this->config = $config;
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $this->pdo = new PDO($dsn, $config['username'], $config['password'], $opts);
        $this->whitelist = $config['whitelist_tables'] ?? [];
    }

    public function isTableAllowed(string $table): bool
    {
        return empty($this->whitelist) || in_array($table, $this->whitelist, true);
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }
    public function commit(): void
    {
        $this->pdo->commit();
    }
    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    // convenience
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
