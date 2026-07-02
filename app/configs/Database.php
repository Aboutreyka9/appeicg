<?php

declare(strict_types=1);

namespace App\configs;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;


    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host     = $_ENV['DB_HOST']     ?? '127.0.0.1';
            $port     = $_ENV['DB_PORT']     ?? '3306';
            $dbname   = $_ENV['DB_NAME']     ?? 'db_eicg';
            $user     = $_ENV['DB_USER']     ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Ne jamais exposer le message d'erreur complet en prod
                $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
                $msg   = $debug ? $e->getMessage() : 'Erreur de connexion à la base de données.';
                http_response_code(500);
                echo json_encode(['success' => false, 'code' => 500, 'message' => $msg]);
                exit;
            }
        }

        return self::$instance;
    }

    // Empêcher l'instanciation directe
    private function __construct() {
           // Charger les variables d'environnement une seule fois
        $dotenv = Dotenv::createImmutable(__DIR__ . THREE_PIP);
        $dotenv->load();
    }
    private function __clone() {}
}
