# Vite & Gourmand

Application web de commande de menus traiteur en ligne, développée pour l'entreprise "Vite & Gourmand" (Bordeaux) dans le cadre de l'ECF Développeur Web et Web Mobile.

## Stack technique

- **Front-end** : HTML5, Bootstrap 5 (surchargé avec la charte graphique du projet), JavaScript vanilla (fetch API)
- **Back-end** : PHP 8.2 orienté objet, PDO pour l'accès MySQL
- **Base de données relationnelle** : MySQL 8.0
- **Base de données NoSQL** : MongoDB 7 (statistiques admin)
- **Conteneurisation** : Docker / Docker Compose

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et lancé

## Installation et lancement de l'environnement

1. Cloner le dépôt

   ```bash
   git clone <url_du_depot>
   cd vite-gourmand
   ```

2. Copier le fichier d'environnement d'exemple

   ```bash
   cp .env.example .env
   ```

3. Lancer les conteneurs

   ```bash
   docker compose up -d --build
   ```

4. Accès aux services :
   - Application web : http://localhost:8080
   - phpMyAdmin : http://localhost:8081 (utilisateur : `vg_user`, mot de passe : voir `.env`)
   - MongoDB (via Compass ou mongosh) : `mongodb://localhost:8090`

Les tables MySQL et le jeu de données de test sont importés **automatiquement** au premier démarrage du conteneur `mysql` (scripts dans `database/`).

Pour initialiser la collection MongoDB (à faire manuellement une première fois) :

```bash
docker exec -it vg_mongo mongosh --file /dev/stdin < database/03_init_mongodb.js
```

## Arrêter l'environnement

```bash
docker compose down
```

Pour tout réinitialiser (⚠️ supprime les données) :

```bash
docker compose down -v
```

## Arborescence du projet

```
vite-gourmand/
├── docker-compose.yml
├── .env
├── app/                 # Code PHP (Dockerfile + application : config, src, views, public)
├── database/            # Scripts SQL et MongoDB
└── docs/                # Documentation ECF (analyse, technique, déploiement)
```

## Justification des choix techniques

Le choix de la pile technique répond à plusieurs objectifs :

- **PHP 8.2 + PDO** : simplicité d'implémentation, compatibilité avec l'environnement de formation et bonnes pratiques de sécurisation.
- **MySQL** : adapté aux données transactionnelles du restaurant, comme les commandes, les utilisateurs et les produits.
- **MongoDB** : utile pour stocker des données de statistiques, de logs ou d'observations sans imposer une structure SQL rigide.
- **Docker Compose** : permet de reproduire rapidement l'environnement local et de faciliter le déploiement.

Cette combinaison est pertinente pour un projet de formation, car elle permet d'illustrer à la fois la logique d'un développement web classique et l'intégration d'une base de données NoSQL.

## Sécurité

- Mots de passe utilisateurs hashés (bcrypt via `password_hash()`)
- Requêtes préparées (PDO) contre les injections SQL
- Validation de schéma MongoDB (`$jsonSchema`)
- Variables sensibles isolées dans `.env` (non versionné)

## Documentation ECF

La documentation complémentaire est disponible dans le dossier [docs](docs) :

- [docs/manuel-utilisation.md](docs/manuel-utilisation.md)
- [docs/documentation-technique.md](docs/documentation-technique.md)
- [docs/gestion-projet.md](docs/gestion-projet.md)
- [docs/deploiement.md](docs/deploiement.md)
- [docs/maquettes.md](docs/maquettes.md)
- [docs/diagrammes.md](docs/diagrammes.md)
