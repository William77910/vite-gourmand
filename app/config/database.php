<?php

/**
 * Connexion MySQL via PDO (pattern singleton : une seule connexion
 * réutilisée dans toute l'application).
 *
 * Sécurité :
 * - ATTR_EMULATE_PREPARES => false : force PDO à utiliser de vraies
 *   requêtes préparées côté serveur MySQL (protection anti-injection SQL)
 * - ATTR_ERRMODE => EXCEPTION : toute erreur SQL lève une exception,
 *   plus facile à intercepter et logger proprement
 */
class Database
{
    // Instance unique de PDO
    private static ?PDO $instance = null;
    // Récupère l'instance PDO (connexion à la base de données)
    public static function getConnection(): PDO
    {
        // Si l'instance n'existe pas encore, on la crée
        if (self::$instance === null) {
            $host     = getenv('DB_HOST');
            $dbname   = getenv('DB_NAME');
            $user     = getenv('DB_USER');
            $password = getenv('DB_PASSWORD');

            // On utilise try/catch pour intercepter les erreurs de connexion
            try {
                self::$instance = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $password,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES    => false,
                    ]
                );
            } catch (PDOException $e) {
                // En production, ne jamais afficher $e->getMessage() au visiteur
                die("Erreur de connexion à la base de données.");
            }
        }

        return self::$instance;
    }
}
