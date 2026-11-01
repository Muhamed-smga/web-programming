<?php

namespace Sarajevo2B\Backend\Config;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(array $config): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $database = $config['database'];
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $database['host'],
            $database['port'],
            $database['name']
        );

        try {
            self::$connection = new PDO($dsn, $database['user'], $database['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $exception) {
            throw new RuntimeException('Unable to connect to the configured database.', 0, $exception);
        }

        return self::$connection;
    }
}
