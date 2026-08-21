# Documentation Technique

## 🏗 Architecture

L'application repose sur une **persistance polyglotte** :

- **MySQL (Relationnel) :** Garantit l'intégrité des données transactionnelles (Commandes, Stocks, Utilisateurs) grâce aux propriétés ACID.
- **MongoDB (Documentaire) :** Dédié à l'analyse de données (Business Intelligence). L'utilisation de pipelines d'agrégation permet de générer des statistiques complexes sans impacter les performances de la base de production.

## 🔒 Sécurité (Normes OWASP)

- **Injections SQL :** Utilisation systématique de l'interface PDO avec requêtes préparées.
- **Failles XSS :** Échappement de toutes les données de sortie via `htmlspecialchars()`.
- **CSRF :** Implémentation de jetons de session uniques pour chaque formulaire sensible.
- **Hachage :** Utilisation de l'algorithme `BCRYPT` pour le stockage des mots de passe.

## 🌐 Résilience Réseau

Pour le calcul des frais de livraison, un système hybride a été mis en place :

1. Appel à l'API Adresse Gouv.fr via cURL.
2. En cas d'échec (timeout ou TLS error), bascule automatique sur un simulateur de coordonnées interne (Mock) pour garantir la continuité de service.
