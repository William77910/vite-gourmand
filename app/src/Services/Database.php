<?php
// app/src/Services/Database.php

namespace App\Services;

use PDO;
use Exception;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $host = getenv('DB_HOST') ?: 'mysql';
        $db   = getenv('DB_NAME') ?: 'vite_gourmand';
        $user = getenv('DB_USER') ?: 'vg_user';
        $pass = getenv('DB_PASSWORD') ?: 'vg_password';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
