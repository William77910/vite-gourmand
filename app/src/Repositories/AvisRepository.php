<?php
// app/src/Repositories/AvisRepository.php

namespace App\Repositories;

use App\Services\Database;

class AvisRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDerniersAvisValides(int $limit) {
        $stmt = $this->db->prepare("SELECT a.*, u.prenom, u.nom, m.titre as titre_menu 
                                    FROM avis a 
                                    JOIN commande c ON a.id_commande = c.id_commande
                                    JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
                                    JOIN menu m ON c.id_menu = m.id_menu
                                    WHERE a.valide = 1 
                                    ORDER BY a.date_creation DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
