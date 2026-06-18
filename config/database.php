<?php

class Database
{
    private const HOST = '127.0.0.1';
    private const PORT = '3306';
    private const NAME = 'magerwa_vts';
    private const USER = 'root';
    private const PASS = '';

    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . self::HOST . ';port=' . self::PORT . ';dbname=' . self::NAME . ';charset=utf8mb4';

            try {
                self::$instance = new PDO($dsn, self::USER, self::PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
