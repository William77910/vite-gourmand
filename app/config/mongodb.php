<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Database;

/**
 * Connexion MongoDB via la librairie officielle mongodb/mongodb.
 * Pattern singleton, comme pour la connexion MySQL.
 */
class MongoConnection
{
    // Instance unique de Client MongoDB
    private static ?Client $client = null;
    // Récupère l'instance Client MongoDB
    public static function getClient(): Client
    {
        // Si l'instance n'existe pas encore, on la crée
        if (self::$client === null) {
            $uri = getenv('MONGO_URI'); // Priorité à une URI complète (Atlas)
            if (!$uri) {
                $host = getenv('MONGO_HOST');
                $port = getenv('MONGO_PORT');
                $uri = "mongodb://{$host}:{$port}";
            }
            self::$client = new Client($uri);
        }

        return self::$client;
    }

    public static function getDatabase(): Database
    {
        return self::getClient()->selectDatabase(getenv('MONGO_DB'));
    }
}
