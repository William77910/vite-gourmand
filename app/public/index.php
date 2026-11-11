<?php
// Charger l'autoloader généré par Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\ApiController;

// On récupère le paramètre 'route' depuis l'URL
$route = $_GET['route'] ?? 'accueil';

// --- ROUTAGE ---
switch ($route) {

  case 'api-menus':
    // Appel de l'API de filtrage
    (new ApiController())->filterMenus();
    break;

  case 'api-livraison':
    (new ApiController())->getFraisLivraison();
    break;

  case 'accueil':
  default:
    // Appel de la page d'accueil via le contrôleur
    (new HomeController())->index();
    break;
}
