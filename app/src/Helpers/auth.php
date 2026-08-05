<?php

/**
 * Vérifie que l'utilisateur connecté a l'un des rôles autorisés.
 * Redirige vers la connexion (si non connecté) ou l'accueil (si rôle
 * insuffisant) sinon. La vérification du rôle se fait TOUJOURS côté
 * serveur : une vérification uniquement en JS/front serait contournable.
 */
function exiger_role(array $rolesAutorises): void
{
    // Démarre la session si elle n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifie si l'utilisateur est connecté et a un rôle autorisé
    if (!isset($_SESSION['id_utilisateur'])) {
        header('Location: /connexion.php');
        exit;
    }

    // Vérifie si le rôle de l'utilisateur est dans la liste des rôles autorisés
    if (!in_array($_SESSION['role'] ?? '', $rolesAutorises, true)) {
        header('Location: /index.php');
        exit;
    }
}
