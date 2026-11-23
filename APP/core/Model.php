<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    protected function rows(string $sql, array $params = []): array
    {
        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    protected function row(string $sql, array $params = []): ?array
    {
        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->fetch() ?: null;
    }
}
