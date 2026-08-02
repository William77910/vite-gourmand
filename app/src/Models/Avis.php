<?php

require_once __DIR__ . '/../../config/database.php';

class Avis
{
    /**
     * Récupère les avis validés pour un menu donné, avec le prénom/nom
     * du client (via la commande associée).
     */
    public static function getByMenu(int $idMenu): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT av.note, av.commentaire, av.date_creation, u.prenom, u.nom
                FROM avis av
                JOIN commande c ON c.id_commande = av.id_commande
                JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                WHERE c.id_menu = :id AND av.valide = 1
                ORDER BY av.date_creation DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idMenu]);
        return $stmt->fetchAll();
    }

    /**
     * Verifie si un avis existe deja pour une commande donnee.
     */
    public static function existePourCommande(int $idCommande): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM avis WHERE id_commande = :id');
        $stmt->execute([':id' => $idCommande]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Depose un avis pour une commande "terminee" appartenant a l'utilisateur.
     * L'avis n'est pas visible publiquement tant qu'un employe ne l'a pas valide.
     */
    public static function create(int $idCommande, int $idUtilisateur, int $note, string $commentaire): bool
    {
        $pdo = Database::getConnection();

        $stmtVerif = $pdo->prepare(
            "SELECT c.id_utilisateur,
                    (SELECT statut FROM historique_statut h WHERE h.id_commande = c.id_commande
                     ORDER BY h.date_modification DESC LIMIT 1) AS statut
             FROM commande c WHERE c.id_commande = :id"
        );
        $stmtVerif->execute([':id' => $idCommande]);
        $commande = $stmtVerif->fetch();

        if (!$commande || $commande['id_utilisateur'] !== $idUtilisateur || $commande['statut'] !== 'terminee') {
            return false;
        }

        if (self::existePourCommande($idCommande)) {
            return false;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO avis (id_commande, note, commentaire, valide) VALUES (:id, :note, :commentaire, 0)'
        );
        $stmt->execute([
            ':id' => $idCommande,
            ':note' => max(1, min(5, $note)),
            ':commentaire' => $commentaire,
        ]);
        return true;
    }
}
