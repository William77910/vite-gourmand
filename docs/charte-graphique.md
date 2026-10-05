# Charte graphique - Vite & Gourmand

## 🎨 Palette de couleurs et variables

Les couleurs de l'application sont définies en variables CSS personnalisées, surchargeant (ou complétant) le thème de Bootstrap 5.3 :

| Couleur         | Hexadécimal | Usage / Variable CSS suggérée                                   |
| :-------------- | :---------- | :-------------------------------------------------------------- |
| **Vert sauge**  | `#7baf8b`   | Couleur principale (`--color-primary` / Boutons primaires)      |
| **Vert foncé**  | `#3f7250`   | Titres, header, éléments de mise en avant (`--color-secondary`) |
| **Beige clair** | `#f4efe6`   | Fond principal de l'application (`--color-background`)          |
| **Jaune clair** | `#fdf9e3`   | Fonds secondaires (cartes, encarts)                             |
| **Blanc**       | `#ffffff`   | Texte sur fond sombre, conteneurs (`--color-white`)             |
| **Gris chaud**  | `#6f6f6f`   | Texte secondaire, bordures                                      |
| **Gris clair**  | `#e6e6e6`   | Éléments désactivés, séparateurs                                |
| **Orange**      | `#e8a15b`   | Alertes, avertissements (Mapping Bootstrap `$warning`)          |
| **Rouge**       | `#d96c6c`   | Erreurs, actions destructives (Mapping Bootstrap `$danger`)     |

## 🔤 Typographies

Les polices sont importées et définies dans notre feuille de style globale :

- **Titres (H1 - H6)** : `Montserrat`, sans-serif (Apporte modernité et lisibilité).
- **Corps de texte (p, span)** : `Open Sans`, sans-serif (Optimisé pour la lecture sur écran).
- **Style décoratif** : `Italianno`, cursive (Utilisé avec parcimonie pour l'identité "Traiteur/Gourmand").

## 👁️ Principes visuels & Accessibilité (RGAA)

- **Identité** : Style sobre, chaleureux et naturel, reflétant le positionnement du traiteur.
- **Accessibilité** : Vérification des contrastes entre la couleur du texte et la couleur de fond (Respect des normes d'accessibilité RGAA/WCAG niveau AA minimum).
- **Responsive Design** : Interface fluide et lisible adaptée aux résolutions mobiles, tablettes et bureaux (utilisation du système de Grid/Flexbox de Bootstrap).

## 🛠️ Usage attendu (Guidelines Dev)

- Les appels à l'action (CTA) principaux utilisent le Vert sauge.
- Les paragraphes et textes standards utilisent Open Sans avec la couleur Gris chaud ou Noir/Vert foncé selon le fond.
- Ne pas utiliser les couleurs de validation (Vert/Rouge) à des fins purement esthétiques pour ne pas confondre l'utilisateur lors du tunnel de commande.
