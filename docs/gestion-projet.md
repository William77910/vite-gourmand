# Gestion de Projet & Workflow de Développement

## Méthodologie : Approche Iterative

Le projet "Vite & Gourmand" a été conduit selon une approche itérative, s'inspirant des principes **Agile**, permettant de valider chaque brique technique avant de passer à la suivante.

1. **Phase de Conception :** Rédaction des User Stories (Besoins utilisateurs), élaboration du MCD et maquettage (Wireframes).
2. **Configuration de l'Environnement :** Mise en place de la stack **Docker** pour garantir la parité entre les environnements de développement et de déploiement.
3. **Développement :** Architecture MVC, système d'authentification et gestion CRUD des menus.
4. **Logique Métier :** Tunnel de commande, transactions SQL, calcul de frais via API et machine à états des commandes.
5. **Analytique & Sécurité :** Synchronisation MongoDB, pipelines d'agrégation, audits de sécurité OWASP et implémentation du RGPD.
6. **Recette & Documentation :** Tests fonctionnels (comptes tests) et finalisation des dossiers techniques.

## Stratégie de Versioning (GitFlow)

J'ai utilisé **Git** avec une stratégie de branches rigoureuse pour garantir la stabilité du code :

- **`main`** : Branche de production. Contient uniquement le code stable et testé.
- **`develop`** : Branche d'intégration. C'est ici que les nouvelles fonctionnalités sont fusionnées.
- **`feature/nom-feature`** : Branches éphémères pour le développement de fonctionnalités spécifiques (ex: `feature/cart-logic`, `feature/mongo-sync`).
- **Commits :** Utilisation de messages explicites (ex: `feat: add CSRF protection to contact form`) pour faciliter le suivi des modifications.

## Environnement de Développement (DevOps)

L'utilisation de **Docker** et **Docker-Compose** a été centrale pour :

- **Isoler les services** (Apache/PHP, MySQL, MongoDB, phpMyAdmin).
- **Garantir la portabilité** : Le projet peut être lancé sur n'importe quelle machine avec une seule commande (`docker-compose up`).
- **Standardiser les versions** : Éviter les conflits "Ça marche sur ma machine" en fixant les versions (PHP 8.2, MySQL 8.0).

## Suivi des tâches

L'organisation des tâches a été pilotée via un tableau **Trello**, structuré en colonnes :

- **Backlog** : Toutes les fonctionnalités prévues.
- **En cours** : Tâches en cours de développement.
- **À Tester** : Fonctionnalités prêtes pour la revue de code/tests.
- **Terminé** : Fonctionnalités validées et documentées.
