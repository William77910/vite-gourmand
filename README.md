# ECF : Vite & Gourmand - Traiteur Gastronomique

Ce projet est réalisé dans le cadre du titre professionnel **Développeur Web et Web Mobile (DWWM)**.
Il s'agit d'une application de gestion pour un traiteur bordelais, intégrant la vente en ligne et l'analyse de données.

## 🛠 Stack Technique

- **Serveur Web :** Apache / PHP 8.x
- **Base de données Relationnelle :** MySQL 8.0 (Gestion des commandes et du catalogue)
- **Base de données NoSQL :** MongoDB 7.0 (Analytique et Statistiques)
- **Front-end :** Bootstrap 5.3, JavaScript (Chart.js, Geocoding API)
- **Environnement :** Docker Desktop

## 🚀 Installation locale

1. **Clonage du dépôt :**

   ```bash
   git clone [TON_LIEN_GITHUB]
   cd vite-gourmand
   ```

2. Lancement de l'environnement :
   Assurez-vous que Docker Desktop est lancé, puis :

   docker-compose up -d --build

3. Initialisation de la base MySQL :
   Accédez à phpMyAdmin : http://localhost:8081
   Importez le fichier database/vite_gourmand_final.sql dans la base vite_gourmand.

4. Initialisation de MongoDB :
   Dans le terminal Docker (Exec) du conteneur vg_mongo :

   mongosh vite_gourmand_stats database/03_init_mongodb.js

🔐 Identifiants de Test (Démonstration)
Rôle Email Mot de passe
Administrateur josegourmand@mail.com [Azerty123!]
Employé julie@vitegourmand.com [Azerty123!]
Client johndoe@test.com [Azerty123!]

💡 Points clés du projet
Architecture Polyglotte : Utilisation de MySQL pour les transactions ACID et MongoDB pour l'agrégation de données analytiques.
Sécurité : Protection contre les failles XSS (htmlspecialchars), CSRF (tokens), et hachage des mots de passe (BCRYPT).
Résilience : Système de géolocalisation hybride (API Adresse Gouv.fr + Simulateur interne en cas de coupure réseau).
Responsive : Design adaptatif et accessible (Grid CSS, RGAA).
