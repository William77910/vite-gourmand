<?php

require_once __DIR__ . '/../../config/database.php';

class Horaire
{
  /**
   * Récupère tous les horaires triés par jour.
   */
  public static function getAll(): array
  {
    $pdo = Database::getConnection();
    // On utilise l'ordre naturel des jours via le champ ENUM
    $sql = "SELECT * FROM horaire ORDER BY FIELD(jour_semaine, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'), service";
    return $pdo->query($sql)->fetchAll();
  }

  /**
   * Met à jour un créneau horaire avec option de commentaire.
   */
  public static function update(int $id, array $data): void
  {
    $pdo = Database::getConnection();
    $sql = "UPDATE horaire SET 
                heure_ouverture = :ouverture, 
                heure_fermeture = :fermeture, 
                ouvert = :ouvert,
                commentaire_fermeture = :commentaire 
                WHERE id_horaire = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':ouverture' => ($data['ouvert'] && $data['ouverture']) ? $data['ouverture'] : null,
      ':fermeture' => ($data['ouvert'] && $data['fermeture']) ? $data['fermeture'] : null,
      ':ouvert' => $data['ouvert'] ? 1 : 0,
      ':commentaire' => $data['commentaire'] ?: null,
      ':id' => $id
    ]);
  }
}
