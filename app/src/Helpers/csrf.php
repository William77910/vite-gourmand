<?php

/**
 * Protection CSRF : un jeton unique par session est inclus dans chaque
 * formulaire sensible (champ caché) et vérifié à la soumission.
 * Cela empêche un site tiers de soumettre un formulaire à votre place
 * à l'insu de l'utilisateur.
 */
function generer_token_csrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifier_token_csrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}
