<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modèle Utilisateur : Gère les comptes clients, employés et administrateurs.
 * Centralise la sécurité des accès et la persistance des profils.
 */
class Utilisateur
{
    /**
     * Recherche un utilisateur par son email (pour la connexion).
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    /**
     * Récupère un utilisateur par son ID unique.
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    /**
     * Récupère tout le personnel (Employés et Admins).
     * Exigence Page 9 du PDF.
     */
    public static function getAllPersonnel(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id_utilisateur, nom, prenom, email, role, actif, date_creation 
                FROM utilisateur 
                WHERE role IN ('employe', 'administrateur') 
                ORDER BY role ASC, nom ASC";
        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Active ou désactive un compte (Droit de dé-provisionnement).
     */
    public static function updateStatus(int $id, int $etat): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE utilisateur SET actif = :etat WHERE id_utilisateur = :id");
        return $stmt->execute([':etat' => $etat, ':id' => $id]);
    }

    /**
     * Crée un nouvel utilisateur. 
     * Utilise l'opérateur de fusion null (??) pour éviter les erreurs d'index.
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO utilisateur
                    (nom, prenom, email, mot_de_passe, telephone, adresse_postale, code_postal, ville, role, actif)
                VALUES
                    (:nom, :prenom, :email, :mot_de_passe, :telephone, :adresse_postale, :code_postal, :ville, :role, 1)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom'             => $data['nom'] ?? '',
            ':prenom'          => $data['prenom'] ?? '',
            ':email'           => $data['email'] ?? '',
            ':mot_de_passe'    => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            ':telephone'       => $data['telephone'] ?? null,
            ':adresse_postale' => $data['adresse_postale'] ?? null,
            ':code_postal'     => $data['code_postal'] ?? null,
            ':ville'           => $data['ville'] ?? null,
            ':role'            => $data['role'] ?? 'utilisateur'
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour les informations de profil.
     */
    public static function update(int $id, array $data): void
    {
        $pdo = Database::getConnection();
        $sql = "UPDATE utilisateur SET
                    nom = :nom, prenom = :prenom, telephone = :telephone,
                    adresse_postale = :adresse_postale, code_postal = :code_postal, ville = :ville
                WHERE id_utilisateur = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':telephone' => $data['telephone'],
            ':adresse_postale' => $data['adresse_postale'],
            ':code_postal' => $data['code_postal'],
            ':ville' => $data['ville'],
            ':id' => $id,
        ]);
    }

    /**
     * Met à jour uniquement l'email.
     */
    public static function updateEmail(int $id, string $nouvelEmail): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE utilisateur SET email = :email WHERE id_utilisateur = :id');
        $stmt->execute([':email' => $nouvelEmail, ':id' => $id]);
    }

    /**
     * Met à jour le mot de passe (hachage sécurisé).
     */
    public static function updatePassword(int $id, string $nouveauMdp): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = :mdp WHERE id_utilisateur = :id");
        $stmt->execute([
            ':mdp' => password_hash($nouveauMdp, PASSWORD_DEFAULT),
            ':id'  => $id
        ]);
    }
}
