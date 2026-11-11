<?php
// app/src/Controllers/HomeController.php

namespace App\Controllers;

use App\Repositories\AvisRepository;

class HomeController {
    public function index() {
        $avisRepo = new AvisRepository();
        $avisValides = $avisRepo->getDerniersAvisValides(3);
        
        // Charger la vue (assure-toi que le chemin est correct depuis ton index)
        require_once __DIR__ . '/../../views/home.php';
    }
}
