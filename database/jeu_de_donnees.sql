-- Insertion des données
USE vite_gourmand;

INSERT INTO `allergene` (`id_allergene`, `nom`) VALUES
(6, 'Arachide'),(9, 'Céleri'),(5, 'Crustacés'),(3, 'Fruits à coque'),(12, 'Fruits rouges'),
(1, 'Gluten'),(2, 'Lactose'),(17, 'Lupin'),(11, 'Mollusques'),(15, 'Moutarde'),
(4, 'Oeuf'),(14, 'Poisson'),(16, 'Sésame'),(13, 'Soja'),(18, 'Sulfites'),
(10, 'Traces possible de fruits à coque');

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `adresse_postale`, `code_postal`, `ville`, `role`, `actif`) VALUES
(5, 'Gourmand', 'José', 'josegourmand@mail.com', '$2y$10$gazAvdU3J4GDzlEX36P7ROEpHWxIXP.HBjI9Q8kkjXedB/bFA4N02', '0605040302', '1 rue de la ville', '33000', 'Bordeaux', 'administrateur', 1),
(10, 'Gourmand', 'Julie', 'julie@vitegourmand.com', '$2y$10$GTO4fVktk22bWtEKdOxoi.63XmhqP/H4p65UeKwToZRMHJQFmqDJ2', '0605040302', '1, rue de la ville', '33000', 'Bordeaux', 'employe', 1),
(11, 'Doe', 'John', 'johndoe@test.com', '$2y$10$mecRkCUsJ8suARiTVuX2ZezVZdnBlEwFcTI5xZWS8x/ymLCDZQRMi', '0602030405', '5, rue du centre', '33000', 'Bordeaux', 'utilisateur', 1);

INSERT INTO `menu` (`id_menu`, `titre`, `theme`, `regime`, `nb_personnes_min`, `prix_personne`, `stock_disponible`) VALUES
(1, 'Menu Méditerranéen', 'classique', 'classique', 4, 25.00, 7),
(2, 'Menu Végétarien', 'classique', 'vegetarien', 4, 20.00, 8),
(3, 'Menu Gourmet', 'evenement', 'classique', 6, 30.00, 9),
(14, 'Menu Barbecue', 'classique', 'classique', 4, 15.00, 5);

INSERT INTO `image` (`id_image`, `url_image`) VALUES
(1, '/images/menus/plat-mediterraneen.jpg'),(2, '/images/menus/entree-mediterraneen.jpg'),
(3, '/images/menus/dessert-mediterraneen.jpg'),(4, '/images/menus/entree-vegetarien.jpg'),
(5, '/images/menus/plat-vegetarien.jpg'),(6, '/images/menus/dessert-vegetarien.jpg'),
(7, '/images/menus/entree-gourmet.jpg'),(8, '/images/menus/plat-gourmet.jpg'),
(9, '/images/menus/dessert-gourmet.jpg'),(45, '/images/menus/grillade.jpg'),(43, '/images/menus/cafe.jpg');

INSERT INTO `plat` (`id_plat`, `nom`, `description`, `type_plat`, `id_image`) VALUES
(1, 'Brochettes de poulet', 'Brochettes marinées', 'plat', 1),
(2, 'Salade Grecque', 'Salade fraîche', 'entree', 2),
(3, 'Baklava', 'Dessert traditionnel', 'dessert', 3),
(38, 'Grillade', '', 'plat', 45),
(39, 'Café gourmand', 'Café et pâtisseries', 'dessert', 43);

INSERT INTO `horaire` (`id_horaire`, `jour_semaine`, `service`, `heure_ouverture`, `heure_fermeture`, `ouvert`) VALUES
(1, 'lundi', 'midi', '12:00:00', '14:00:00', 1),
(2, 'lundi', 'soir', '18:00:00', '23:00:00', 1);
