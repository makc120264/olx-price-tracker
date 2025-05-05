<?php

namespace App;

use PDO;
use Dotenv\Dotenv;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();

            $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8mb4';
            self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        return self::$instance;
    }
}
