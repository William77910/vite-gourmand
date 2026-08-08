-- ============================================================
-- Vite & Gourmand - Nettoyage + reinsertion des donnees de test avis
-- A executer EN UNE SEULE FOIS (selectionner tout avant de cliquer "Executer")
-- ============================================================

USE vite_gourmand;

-- Supprime la commande de test orpheline (sans historique de statut)
-- creee lors de la tentative precedente qui a echoue
DELETE c FROM commande c
LEFT JOIN historique_statut h ON h.id_commande = c.id_commande
WHERE h.id_commande IS NULL
  AND c.adresse_livraison = '3 rue Sainte-Catherine'
  AND c.date_prestation = '2026-07-01';

-- Nouvelle commande de test, deja "terminee"
INSERT INTO commande (id_utilisateur, id_menu, nb_personnes, date_prestation, heure_prestation, adresse_livraison, ville_livraison, code_postal_livraison, frais_livraison, reduction_appliquee, prix_total, mode_paiement)
VALUES (3, 2, 4, '2026-07-01', '19:00:00', '3 rue Sainte-Catherine', 'Bordeaux', '33000', 5.00, 0, 85.00, 'carte');

INSERT INTO historique_statut (id_commande, statut, date_modification)
SELECT id_commande, 'en_attente', '2026-06-25 10:00:00' FROM commande
WHERE adresse_livraison = '3 rue Sainte-Catherine' AND date_prestation = '2026-07-01'
ORDER BY id_commande DESC LIMIT 1;

INSERT INTO historique_statut (id_commande, statut, date_modification)
SELECT id_commande, 'accepte', '2026-06-25 14:00:00' FROM commande
WHERE adresse_livraison = '3 rue Sainte-Catherine' AND date_prestation = '2026-07-01'
ORDER BY id_commande DESC LIMIT 1;

INSERT INTO historique_statut (id_commande, statut, date_modification)
SELECT id_commande, 'terminee', '2026-07-01 21:00:00' FROM commande
WHERE adresse_livraison = '3 rue Sainte-Catherine' AND date_prestation = '2026-07-01'
ORDER BY id_commande DESC LIMIT 1;

-- Avis en attente de moderation (valide = 0)
INSERT INTO avis (id_commande, note, commentaire, valide)
SELECT id_commande, 4, 'Très bon menu, service rapide, je recommande !', 0 FROM commande
WHERE adresse_livraison = '3 rue Sainte-Catherine' AND date_prestation = '2026-07-01'
ORDER BY id_commande DESC LIMIT 1;
