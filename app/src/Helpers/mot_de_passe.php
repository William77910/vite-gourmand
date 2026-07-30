<?php

/**
 * Vérifie chaque règle de complexité du mot de passe séparément.
 * Utile pour afficher un détail des critères non respectés.
 */
function valider_mot_de_passe(string $mdp): array
{
    return [
        'longueur'  => strlen($mdp) >= 10,
        'majuscule' => (bool) preg_match('/[A-Z]/', $mdp),
        'minuscule' => (bool) preg_match('/[a-z]/', $mdp),
        'chiffre'   => (bool) preg_match('/[0-9]/', $mdp),
        'special'   => (bool) preg_match('/[^A-Za-z0-9]/', $mdp),
    ];
}

/**
 * Retourne vrai uniquement si toutes les règles sont respectées.
 */
function mot_de_passe_valide(string $mdp): bool
{
    return !in_array(false, valider_mot_de_passe($mdp), true);
}
