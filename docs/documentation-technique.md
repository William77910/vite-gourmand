# Documentation technique

## Architecture

Le projet repose sur une architecture PHP simple et modulaire :

- Front-end : HTML5, CSS, Bootstrap 5, JavaScript vanilla
- Back-end : PHP 8.2
- Base de données relationnelle : MySQL
- Base de données NoSQL : MongoDB
- Conteneurisation : Docker et Docker Compose

## Structure logique

- app/config : configuration des connexions aux bases de données
- app/src/Controllers : contrôleurs de l'application
- app/src/Models : modèles de données
- app/src/Helpers : fonctions utilitaires
- app/views : templates et vues
- app/public : point d'entrée web et fichiers statiques

## Choix techniques

- PHP a été retenu pour sa simplicité d'intégration et sa conformité avec l'environnement de formation.
- MySQL est utilisé pour les données transactionnelles du restaurant.
- MongoDB est utilisé pour les usages analytiques et de statistiques.
- Docker permet de reproduire facilement l'environnement local.
