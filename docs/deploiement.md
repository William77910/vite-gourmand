# Documentation de déploiement - Vite & Gourmand

## 📋 Prérequis

- Docker Desktop installé et fonctionnel
- Git
- Un client SQL (type DBeaver ou l'accès via phpMyAdmin inclus dans notre stack)

## 🚀 Procédure d'installation

**1. Récupération du projet :**

git clone [TON_LIEN_GITHUB]
cd vite-gourmand

2. Configuration des variables d'environnement :
   Copiez le fichier d'exemple pour initialiser la configuration. Les valeurs par défaut sont déjà optimisées pour l'environnement Docker.

cp .env.example .env

3. Lancement des services (Apache, PHP, MySQL, MongoDB, phpMyAdmin) :

docker-compose up -d --build

4. Initialisation des bases de données :
   MySQL (Base transactionnelle) :

Accédez à phpMyAdmin : http://localhost:8081
Sélectionnez la base vite_gourmand.
Importez le fichier : database/vite_gourmand_final.sql. (Note : Ce fichier contient la structure de la base et les données de démonstration).

MongoDB (Base analytique) :
Injectez le script d'initialisation directement dans le conteneur depuis votre terminal :

docker exec -i vg_mongo mongosh vite_gourmand_stats < database/03_init_mongodb.js

5. Accès à l'application :
   L'interface client est désormais disponible sur http://localhost:8080.

---

### 2. Fichier `manuel-utilisation.md` (ou à mettre à la fin du README)

# Manuel d'utilisation & Démonstration

## 🔑 Identifiants de test

Pour faciliter l'évaluation du projet, voici les comptes pré-configurés :

| Rôle               | Email                  | Mot de passe |
| :----------------- | :--------------------- | :----------- |
| **Administrateur** | josegourmand@mail.com  | Azerty123!   |
| **Employé**        | julie@vitegourmand.com | Azerty123!   |
| **Client**         | johndoe@test.com       | Azerty123!   |

## 🧭 Parcours de démonstration conseillé (Cycle de vie métier)

Afin d'explorer la logique métier complète de l'application, nous vous invitons à suivre ce scénario :

**1. Le Tunnel de Commande (Client & Employé)**

- Connectez-vous en tant que **Client**.
- Choisissez un menu, ajoutez-le au panier et passez commande (Observez le calcul dynamique des frais de livraison via l'API Adresse / Simulateur interne).
- Déconnectez-vous, puis connectez-vous en tant qu'**Employé**.
- Allez dans la gestion des commandes et faites évoluer le statut de la nouvelle commande jusqu'à "Terminée". _(Cette action décrémente les stocks et synchronise les données avec MongoDB)._
- Repassez côté **Client** : vous pouvez maintenant déposer un avis sur cette commande terminée.
- Repassez côté **Employé** pour modérer (valider) cet avis afin qu'il apparaisse sur la page d'accueil.

**2. Gestion Analytique et Administration (Administrateur)**

- Connectez-vous en tant qu'**Administrateur**.
- Consultez l'onglet "Statistiques Ventes" pour visualiser les données (Chart.js) générées via le pipeline d'agrégation de MongoDB.
- Consultez "Gestion du Personnel" pour tester le Soft Delete (suspension d'un employé).
