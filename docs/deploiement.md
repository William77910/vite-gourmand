# Documentation de déploiement

## Prérequis

- Docker
- Docker Compose
- Git

## Démarrage local

1. Cloner le dépôt
2. Copier le fichier .env.example vers .env
3. Lancer les services :

```bash
docker compose up --build
```

4. Vérifier l'accès à l'application sur http://localhost:8080

## Variables d'environnement

- DB_HOST
- DB_NAME
- DB_USER
- DB_PASSWORD
- MONGO_HOST
- MONGO_PORT
- MONGO_DB
