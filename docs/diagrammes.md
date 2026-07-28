# Diagrammes d'utilisation et de séquence

## Diagramme de cas d'utilisation

- Client : consulter les menus, créer un compte, passer une commande
- Employé : gérer les commandes, préparer les produits
- Administrateur : gérer les menus, consulter les statistiques

## Diagramme de séquence

1. Le client consulte les menus.
2. Le client sélectionne des plats et passe une commande.
3. Le système enregistre la commande dans MySQL.
4. Les données de suivi/statistiques sont envoyées vers MongoDB.
5. L'employé reçoit la commande et la traite.
