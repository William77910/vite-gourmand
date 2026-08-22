# Diagrammes UML - Vite & Gourmand

## 1. Diagramme de Cas d'Utilisation

Ce diagramme illustre les interactions des différents acteurs (Client, Employé, Administrateur) avec les fonctionnalités du système.

```mermaid
flowchart LR
    %% Définition des acteurs (cercles)
    Client((Client))
    Employe((Employé))
    Admin((Administrateur))

    %% Frontière du système (le site web)
    subgraph "Application Vite & Gourmand"
        %% Cas Client
        UC_Compte([Gérer son compte])
        UC_Menu([Consulter les menus])
        UC_Commande([Passer une commande])
        UC_Avis([Déposer un avis])

        %% Cas Employé
        UC_GestionCmd([Gérer les commandes <br> Machine à états])
        UC_ModAvis([Modérer les avis clients])

        %% Cas Administrateur
        UC_GestionMenu([Gérer le catalogue <br> Menus, Plats, Allergènes])
        UC_GestionEquipe([Gérer le personnel <br> Soft Delete])
        UC_Stats([Consulter les statistiques <br> MongoDB])
    end

    %% Connexions Client
    Client --- UC_Compte
    Client --- UC_Menu
    Client --- UC_Commande
    Client --- UC_Avis

    %% Connexions Employé
    Employe --- UC_GestionCmd
    Employe --- UC_ModAvis
    %% L'employé peut aussi consulter les menus
    Employe -.- UC_Menu

    %% Connexions Admin
    Admin --- UC_GestionMenu
    Admin --- UC_GestionEquipe
    Admin --- UC_Stats
    %% L'admin hérite souvent des droits de l'employé
    Admin -.- UC_GestionCmd
```

# Diagrammes d'utilisation et de séquence

## 1. Diagramme de séquence : Le tunnel de commande

Ce diagramme illustre la complexité technique du passage d'une commande, incluant l'appel à une API externe (avec résilience), la gestion transactionnelle (MySQL) et la persistance polyglotte (MongoDB).

```mermaid
sequenceDiagram
    autonumber
    actor C as Client
    participant V as Vue (Interface)
    participant PHP as Contrôleur (PHP)
    participant API as API Gouv / Mock
    participant SQL as MySQL (Transaction)
    participant MDB as MongoDB (Stats)

    C->>V: Valide son panier et adresse
    V->>PHP: POST /validation-commande

    rect rgb(240, 248, 255)
        Note right of PHP: 1. Calcul des frais de port (Résilience)
        PHP->>API: cURL GET (Coordonnées GPS)
        alt API Adresse Gouv HORS LIGNE
            API-->>PHP: Timeout / Erreur HTTP
            PHP->>PHP: Fallback : Utilisation du Mock (Simulateur interne)
        else API Adresse Gouv OK
            API-->>PHP: Réponse JSON
        end
        PHP->>PHP: Calcul distance (Formule Haversine)
    end

    rect rgb(255, 240, 245)
        Note right of PHP: 2. Persistance Transactionnelle
        PHP->>SQL: PDO->beginTransaction()
        SQL-->>PHP: Transaction ouverte

        PHP->>SQL: SELECT stock_disponible FROM menu WHERE id = ? FOR UPDATE
        SQL-->>PHP: Retourne le stock

        alt Stock insuffisant ( == 0 )
            PHP->>SQL: PDO->rollBack()
            PHP-->>V: Erreur : "Stock épuisé"
            V-->>C: Affiche le message d'erreur
        else Stock suffisant ( > 0 )
            PHP->>SQL: INSERT INTO commande (...)
            PHP->>SQL: INSERT INTO historique_statut (statut = 'en_attente')
            PHP->>SQL: UPDATE menu SET stock_disponible = stock_disponible - 1
            PHP->>SQL: PDO->commit()

            Note right of PHP: 3. Persistance Analytique (Asynchrone/Synchronisée)
            PHP-)MDB: Helper Mongo : Insert stats ventes (CA, Menu)

            PHP-->>V: Redirection /succès
            V-->>C: Affiche la confirmation de commande
        end
    end
```
