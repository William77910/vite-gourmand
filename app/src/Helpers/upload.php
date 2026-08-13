<?php

/**
 * Gère le téléversement d'une image : validation du type et de la taille,
 * génération d'un nom de fichier unique (évite les écrasements et les
 * noms de fichiers dangereux), déplacement vers le dossier cible.
 *
 * Retourne le chemin web de l'image (ex: /images/menus/xxxx.jpg) ou null
 * en cas d'échec (aucun fichier envoyé, type non autorisé, erreur).
 */
function televerser_image(array $fichier, string $dossierDestination): ?string
{
    if (empty($fichier['name'])) {
        return null; // aucun fichier envoye, ce n'est pas une erreur
    }

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        // Erreurs frequentes : UPLOAD_ERR_INI_SIZE (fichier trop gros pour
        // upload_max_filesize) ou UPLOAD_ERR_FORM_SIZE (trop gros pour
        // MAX_FILE_SIZE du formulaire)
        error_log('Echec upload image : code erreur PHP ' . $fichier['error']);
        return null;
    }

    $typesAutorises = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tailleMaxOctets = 5 * 1024 * 1024; // 5 Mo

    if ($fichier['size'] > $tailleMaxOctets) {
        return null;
    }

    $typeReel = mime_content_type($fichier['tmp_name']);
    if (!isset($typesAutorises[$typeReel])) {
        return null;
    }

    $extension = $typesAutorises[$typeReel];

    // Le nom de fichier est base sur une empreinte du CONTENU (SHA-256),
    // pas sur un nom aleatoire : deux photos identiques donnent le meme
    // nom de fichier, donc pas de doublon inutile dans le dossier ni en
    // base si la meme photo est reutilisee sur plusieurs plats/menus.
    $empreinte = hash_file('sha256', $fichier['tmp_name']);
    $nomFichier = $empreinte . '.' . $extension;

    $cheminDisque = rtrim($dossierDestination, '/') . '/' . $nomFichier;

    // Si le fichier existe deja (meme contenu deja uploade), pas besoin
    // de le re-ecrire : on reutilise directement le fichier existant.
    if (file_exists($cheminDisque)) {
        return $nomFichier;
    }

    if (!move_uploaded_file($fichier['tmp_name'], $cheminDisque)) {
        return null;
    }

    return $nomFichier;
}
