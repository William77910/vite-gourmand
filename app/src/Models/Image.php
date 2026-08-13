<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modele Image : une image n'existe qu'une seule fois en base
 * (contrainte UNIQUE sur url_image), meme si elle est utilisee par
 * plusieurs plats ou plusieurs menus.
 */
class Image
{
    /**
     * Retourne l'id de l'image si son URL existe deja en base,
     * sinon la cree. Evite les doublons au niveau des lignes,
     * en complement de la deduplication au niveau des fichiers
     * (voir Helpers/upload.php).
     */
    public static function creerOuRecuperer(string $urlImage): int
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('SELECT id_image FROM image WHERE url_image = :url');
        $stmt->execute([':url' => $urlImage]);
        $idExistant = $stmt->fetchColumn();

        if ($idExistant !== false) {
            return (int) $idExistant;
        }

        $stmtInsert = $pdo->prepare('INSERT INTO image (url_image) VALUES (:url)');
        $stmtInsert->execute([':url' => $urlImage]);
        return (int) $pdo->lastInsertId();
    }
}
