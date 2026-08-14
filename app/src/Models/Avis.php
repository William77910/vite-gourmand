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

  /**
   * Avis en attente de moderation (valide = 0), les plus anciens d'abord.
   */
  public static function getEnAttente(): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT av.*, m.titre AS titre_menu, u.nom, u.prenom
                FROM avis av
                JOIN commande c ON c.id_commande = av.id_commande
                JOIN menu m ON m.id_menu = c.id_menu
                JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                WHERE av.valide = 0
                ORDER BY av.date_creation ASC";
    return $pdo->query($sql)->fetchAll();
  }

  /**
   * Avis deja publies (valide = 1), les plus recents d'abord.
   */
  public static function getValides(): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT av.*, m.titre AS titre_menu, u.nom, u.prenom
                FROM avis av
                JOIN commande c ON c.id_commande = av.id_commande
                JOIN menu m ON m.id_menu = c.id_menu
                JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                WHERE av.valide = 1
                ORDER BY av.date_creation DESC";
    return $pdo->query($sql)->fetchAll();
  }

  public static function valider(int $idAvis): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE avis SET valide = 1 WHERE id_avis = :id');
    $stmt->execute([':id' => $idAvis]);
  }

  /**
   * Refuser un avis = le supprimer (pas d'etat "refuse" distinct en base,
   * un avis rejete n'a pas vocation a etre conserve).
   */
  public static function refuser(int $idAvis): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('DELETE FROM avis WHERE id_avis = :id');
    $stmt->execute([':id' => $idAvis]);
  }

  /**
   * Depublie un avis deja valide (fait machine arriere si besoin).
   */
  public static function depublier(int $idAvis): void
  {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE avis SET valide = 0 WHERE id_avis = :id');
    $stmt->execute([':id' => $idAvis]);
  }

  /**
   * Récupère les 3 derniers avis validés pour la page d'accueil.
   */
  public static function getDerniersAvisValides(int $limite = 3): array
  {
    $pdo = Database::getConnection();
    $sql = "SELECT av.*, u.prenom, u.nom, m.titre as titre_menu
                FROM avis av
                JOIN commande c ON c.id_commande = av.id_commande
                JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
                JOIN menu m ON m.id_menu = c.id_menu
                WHERE av.valide = 1
                ORDER BY av.date_creation DESC
                LIMIT :limite";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }
}
