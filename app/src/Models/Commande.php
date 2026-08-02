<?php

require_once __DIR__ . '/../../config/database.php';

class Commande
{
    /**
     * Crée une commande et son premier historique de statut ("en_attente")
     * dans une transaction : soit tout est enregistré, soit rien ne l'est.
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $sql = "INSERT INTO commande
                        (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation,
                         adresse_livraison, ville_livraison, code_postal_livraison,
                         frais_livraison, reduction_appliquee, prix_total, commentaires, mode_paiement)
                    VALUES
                        (:id_utilisateur, :id_menu, :nb_personnes, :date_prestation, :heure_prestation,
                         :adresse_livraison, :ville_livraison, :code_postal_livraison,
                         :frais_livraison, :reduction_appliquee, :prix_total, :commentaires, :mode_paiement)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_utilisateur'        => $data['id_utilisateur'],
                ':id_menu'               => $data['id_menu'],
                ':nb_personnes'          => $data['nb_personnes'],
                ':date_prestation'       => $data['date_prestation'],
                ':heure_prestation'      => $data['heure_prestation'],
                ':adresse_livraison'     => $data['adresse_livraison'],
                ':ville_livraison'       => $data['ville_livraison'],
                ':code_postal_livraison' => $data['code_postal_livraison'],
                ':frais_livraison'       => $data['frais_livraison'],
                ':reduction_appliquee'   => $data['reduction_appliquee'] ? 1 : 0,
                ':prix_total'            => $data['prix_total'],
                ':commentaires'          => $data['commentaires'],
                ':mode_paiement'         => $data['mode_paiement'],
            ]);

            $idCommande = (int) $pdo->lastInsertId();

            $stmtHistorique = $pdo->prepare(
                "INSERT INTO historique_statut (id_commande, statut) VALUES (:id, 'en_attente')"
            );
            $stmtHistorique->execute([':id' => $idCommande]);

            $pdo->commit();
            return $idCommande;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT c.*, m.titre AS titre_menu, m.prix_personne, m.nb_personnes_min
             FROM commande c
             JOIN menu m ON m.id_menu = c.id_menu
             WHERE c.id_commande = :id'
        );
        $stmt->execute([':id' => $id]);
        $commande = $stmt->fetch();
        return $commande ?: null;
    }

    /**
     * Liste des commandes d'un utilisateur, avec le statut actuel de chacune.
     */
    public static function getByUtilisateur(int $idUtilisateur): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT c.*, m.titre AS titre_menu,
                       (SELECT statut FROM historique_statut h
                        WHERE h.id_commande = c.id_commande
                        ORDER BY h.date_modification DESC LIMIT 1) AS statut_actuel,
                       (SELECT COUNT(*) FROM avis a WHERE a.id_commande = c.id_commande) AS a_un_avis
                FROM commande c
                JOIN menu m ON m.id_menu = c.id_menu
                WHERE c.id_utilisateur = :id
                ORDER BY c.date_creation DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    /**
     * Historique horodaté des statuts d'une commande.
     */
    public static function getHistoriqueStatuts(int $idCommande): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT statut, date_modification FROM historique_statut
             WHERE id_commande = :id ORDER BY date_modification ASC'
        );
        $stmt->execute([':id' => $idCommande]);
        return $stmt->fetchAll();
    }

    /**
     * Annule une commande (uniquement si elle appartient bien à l'utilisateur
     * et que son statut est encore "en_attente").
     */
    public static function annuler(int $idCommande, int $idUtilisateur): bool
    {
        $commande = self::getById($idCommande);
        if (!$commande || $commande['id_utilisateur'] !== $idUtilisateur) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmtStatut = $pdo->prepare(
            'SELECT statut FROM historique_statut WHERE id_commande = :id ORDER BY date_modification DESC LIMIT 1'
        );
        $stmtStatut->execute([':id' => $idCommande]);
        $statutActuel = $stmtStatut->fetchColumn();

        if ($statutActuel !== 'en_attente') {
            return false; // trop tard : la commande a deja ete acceptee
        }

        $stmt = $pdo->prepare("INSERT INTO historique_statut (id_commande, statut) VALUES (:id, 'annulee')");
        $stmt->execute([':id' => $idCommande]);
        return true;
    }

    /**
     * Modifie une commande existante (jamais le menu choisi), uniquement si
     * elle appartient a l'utilisateur et n'a pas encore ete acceptee.
     */
    public static function modifier(int $idCommande, int $idUtilisateur, array $data): bool
    {
        $commande = self::getById($idCommande);
        if (!$commande || $commande['id_utilisateur'] !== $idUtilisateur) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmtStatut = $pdo->prepare(
            'SELECT statut FROM historique_statut WHERE id_commande = :id ORDER BY date_modification DESC LIMIT 1'
        );
        $stmtStatut->execute([':id' => $idCommande]);
        if ($stmtStatut->fetchColumn() !== 'en_attente') {
            return false;
        }

        $sql = "UPDATE commande SET
                    nb_personnes = :nb_personnes, date_prestation = :date_prestation,
                    heure_prestation = :heure_prestation, adresse_livraison = :adresse_livraison,
                    ville_livraison = :ville_livraison, code_postal_livraison = :code_postal_livraison,
                    frais_livraison = :frais_livraison, reduction_appliquee = :reduction_appliquee,
                    prix_total = :prix_total, commentaires = :commentaires
                WHERE id_commande = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nb_personnes' => $data['nb_personnes'],
            ':date_prestation' => $data['date_prestation'],
            ':heure_prestation' => $data['heure_prestation'],
            ':adresse_livraison' => $data['adresse_livraison'],
            ':ville_livraison' => $data['ville_livraison'],
            ':code_postal_livraison' => $data['code_postal_livraison'],
            ':frais_livraison' => $data['frais_livraison'],
            ':reduction_appliquee' => $data['reduction_appliquee'] ? 1 : 0,
            ':prix_total' => $data['prix_total'],
            ':commentaires' => $data['commentaires'],
            ':id' => $idCommande,
        ]);
        return true;
    }
}
