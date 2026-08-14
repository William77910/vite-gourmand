<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Traite l'envoi d'un message de contact.
 * 1. Sauvegarde en base de données MySQL
 * 2. Simulation d'envoi d'email
 */
function traiter_message_contact(string $email, string $titre, string $description): bool
{
  try {
    $pdo = Database::getConnection();

    // 1. Sauvegarde en BDD (Table message_contact de ton export SQL)
    $sql = "INSERT INTO message_contact (email, titre, description, traite, date_creation) 
                VALUES (:email, :titre, :description, 0, NOW())";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
      ':email'       => $email,
      ':titre'       => $titre,
      ':description' => $description
    ]);

    // 2. Logique d'envoi de mail
    // Note pour l'ECF : Dans un environnement local (Docker), la fonction mail() 
    // ne fonctionnera pas sans serveur SMTP. On simule donc l'envoi.
    $destinataire = "contact@vitegourmand.com";
    $sujet = "Nouveau contact : " . $titre;
    $corps = "De : $email\n\nMessage :\n$description";
    $headers = "From: no-reply@vitegourmand.com";

    // @mail($destinataire, $sujet, $corps, $headers); // On met un @ pour ne pas afficher d'erreur si pas de serveur mail

    return $result;
  } catch (Exception $e) {
    error_log("Erreur Contact : " . $e->getMessage());
    return false;
  }
}
