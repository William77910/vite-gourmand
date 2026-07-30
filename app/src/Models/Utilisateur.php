<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modèle Utilisateur : centralise les requêtes SQL liées aux comptes.
 * Les mots de passe sont toujours hachés (password_hash) avant stockage,
 * jamais manipulés en clair au-delà de la saisie du formulaire.
 */
class Utilisateur
{
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    /**
     * Crée un compte utilisateur (rôle "utilisateur" par défaut).
     * Retourne l'identifiant du nouveau compte.
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO utilisateur
                    (nom, prenom, email, mot_de_passe, telephone, adresse_postale, code_postal, ville, role, actif)
                VALUES
                    (:nom, :prenom, :email, :mot_de_passe, :telephone, :adresse_postale, :code_postal, :ville, 'utilisateur', 1)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom'             => $data['nom'],
            ':prenom'          => $data['prenom'],
            ':email'           => $data['email'],
            ':mot_de_passe'    => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            ':telephone'       => $data['telephone'],
            ':adresse_postale' => $data['adresse_postale'],
            ':code_postal'     => $data['code_postal'],
            ':ville'           => $data['ville'],
        ]);

        return (int) $pdo->lastInsertId();
    }
}
