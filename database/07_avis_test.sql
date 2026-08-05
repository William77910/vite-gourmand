-- ============================================================
-- Vite & Gourmand - Donnees de test pour la moderation d'avis
-- OPTIONNEL : uniquement pour avoir un avis a tester en attente
-- ============================================================

USE vite_gourmand;

-- Nouvelle commande de test, deja "terminee"
INSERT INTO commande (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation, adresse_livraison, ville_livraison, code_postal_livraison, frais_livraison, reduction_appliquee, prix_total, mode_paiement)
VALUES (3, 2, 4, '2026-07-01', '19:00:00', '3 rue Sainte-Catherine', 'Bordeaux', '33000', 5.00, 0, 85.00, 'carte');

SET @id_commande_test = LAST_INSERT_ID();

INSERT INTO historique_statut (id_commande, statut, date_modification) VALUES
(@id_commande_test, 'en_attente', '2026-06-25 10:00:00'),
(@id_commande_test, 'accepte', '2026-06-25 14:00:00'),
(@id_commande_test, 'terminee', '2026-07-01 21:00:00');

-- Avis en attente de moderation (valide = 0)
INSERT INTO avis (id_commande, note, commentaire, valide)
VALUES (@id_commande_test, 4, 'Très bon menu, service rapide, je recommande !', 0);
