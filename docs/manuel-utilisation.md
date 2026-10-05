# Manuel d'utilisation & Guide d'évaluation

Ce document accompagne l'évaluateur dans la découverte de l'application "Vite & Gourmand". Il propose un parcours optimisé pour tester les fonctionnalités critiques.

## Comptes de test (Prêts à l'emploi)

| Rôle               | Identifiant (Email) | Mot de passe |
| :----------------- | :------------------ | :----------- |
| **Administrateur** | mail admin          | mdp admin    |
| **Employé**        | mail employé        | mdp employé  |
| **Client**         | mail client         | mdp client   |

---

## Parcours de démonstration recommandé

Pour valider l'ensemble des compétences du titre DWWM, nous suggérons de suivre ce cycle métier complet :

### 1. Expérience Client (Front-end & API)

- **Action :** Se connecter avec le compte Client.
- **À vérifier :**
  - Navigation fluide et responsive.
  - Ajout d'un menu au panier.
  - **Le Tunnel de Commande :** Lors de la validation, notez le calcul automatique des frais de livraison (appel API Adresse Gouv).
  - **Remise dynamique :** Observez l'application de la remise de 10% si le nombre de convives est supérieur au minimum requis.

### 2. Gestion Métier (Back-end & Machine à États)

- **Action :** Se déconnecter et se reconnecter en tant qu'**Employé**.
- **À vérifier :**
  - Accès à l'Espace Employé (protégé par rôle).
  - **Cycle de vie :** Faire progresser la commande de "En attente" à "Terminée". Chaque étape est verrouillée par une machine à états stricte.
  - **Gestion des stocks :** Vérifiez dans le catalogue que le stock du menu commandé a bien été décrémenté de 1.

### 3. Interaction & Modération

- **Action :** Repasser côté **Client** pour laisser un Avis sur la commande terminée.
- **Action :** Repasser côté **Employé** pour **Valider l'avis**.
- **Résultat :** L'avis devient visible sur la page d'accueil uniquement après cette validation (Sécurité/Modération).

### 4. Pilotage & Analytique (NoSQL)

- **Action :** Se connecter en tant qu'**Administrateur**.
- **À vérifier :**
  - **Dashboard Stats :** Visualisation des graphiques (Chart.js) alimentés par **MongoDB**. Les données de la commande passée précédemment y sont déjà agrégées.
  - **Sécurité RGPD :** Tester la "Suspension" d'un compte client (Soft Delete / Anonymisation).

---

## 🛠 Points d'attention techniques pour le jury

- **Résilience API :** Si vous coupez votre connexion internet, le système de frais de port basculera automatiquement sur un simulateur interne (Mock).
- **Sécurité :** Tous les formulaires sont protégés contre les failles CSRF et XSS.
- **Architecture :** Séparation stricte entre les données transactionnelles (MySQL) et analytiques (MongoDB).
