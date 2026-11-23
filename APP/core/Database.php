<?php

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $pdo = require dirname(__DIR__) . '/config/connect_db.php';

            self::$connection = $pdo;
        }

        return self::$connection;
    }
}