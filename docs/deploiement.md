# Documentation de déploiement

## 📋 Prérequis

- Docker Desktop installé et fonctionnel
- Git
- Un client SQL (type DBeaver ou accès via phpMyAdmin inclus)

## 🚀 Procédure d'installation

1. **Récupération du projet :**

   ```bash
   git clone [TON_LIEN_GITHUB]
   cd vite-gourmand

   Configuration :
   Copiez le fichier .env.example en .env et adaptez les secrets si nécessaire (les valeurs par défaut sont configurées pour Docker).

   Lancement des services :

   docker-compose up -d --build

   Initialisation des données (MySQL) :
   Accédez à phpMyAdmin : http://localhost:8081
   Sélectionnez la base vite_gourmand.
   Importez le fichier : database/vite_gourmand_final.sql.
   Note : Ce fichier contient la structure et les données de démonstration.

   Initialisation des données (MongoDB) :
   Ouvrez un terminal dans le conteneur vg_mongo ou utilisez la commande :

   docker exec -it vg_mongo mongosh vite_gourmand_stats database/03_init_mongodb.js

   Accès à l'application :
   L'interface est disponible sur http://localhost:8080.
   ```

### 2. Mise à jour de `manuel-utilisation.md`

Ici, nous devons mettre tes **vrais** identifiants de test pour que le jury puisse suivre tes 3 cycles de démo.

**Remplace le contenu par celui-ci :**

```markdown
# Manuel d'utilisation

## 🔑 Identifiants de test (Démonstration)

| Rôle               | Email                  | Mot de passe |
| :----------------- | :--------------------- | :----------- |
| **Administrateur** | josegourmand@mail.com  | Azerty123!   |
| **Employé**        | julie@vitegourmand.com | Azerty123!   |
| **Client**         | johndoe@test.com       | Azerty123!   |

## 🧭 Parcours de démonstration conseillé

### 1. Cycle de commande complet

- Connectez-vous en tant que **Client**.
- Choisissez un menu et passez commande (Vérifiez le calcul des frais de port).
- Connectez-vous en tant qu'**Employé**.
- Gérez la commande jusqu'au statut "Terminée".
- Repassez côté **Client** pour déposer un avis.
- Repassez côté **Employé** pour valider l'avis.

### 2. Gestion et Statistiques

- Connectez-vous en tant qu'**Administrateur**.
- Consultez l'onglet "Statistiques Ventes" pour visualiser les données synchronisées dans MongoDB.
- Gérez les accès du personnel via "Gestion Personnel".
```
