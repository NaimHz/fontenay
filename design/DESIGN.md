# Design System — Fontenay

Direction artistique extraite de la maquette Figma (thème produit : sombre & gastronomique).
Ces tokens sont la source de vérité partagée par le site de réservation et l'app staff.

## Palette

| Token             | Hex        | Usage                                                                 |
|-------------------|------------|-----------------------------------------------------------------------|
| `bg`              | `#0D0D0D`  | Fond général (noir)                                                    |
| `surface`         | `#1C1C1C`  | Cartes, panneaux, champs                                               |
| `surface-2`       | `#262626`  | Survol, lignes alternées                                               |
| `border`          | `#333333`  | Bordures discrètes                                                     |
| `gold`            | `#C8A06A`  | Marque FONTENAY, accents, en-têtes, panneau du site de réservation    |
| `gold-soft`       | `#E2C9A0`  | Or clair (textes sur fond sombre, hover)                              |
| `green`           | `#27AE60`  | Table **libre**, plat **sélectionné**, **Ajout commande**, **Valider**|
| `red`             | `#E74C3C`  | Table **occupée/réservée**, **Facture & clôture**, **Annuler**        |
| `text`            | `#F5F5F0`  | Texte principal                                                       |
| `text-muted`      | `#A0A0A0`  | Texte secondaire                                                      |

## Code couleur des tables (repris partout)

- 🟢 **Vert** = libre
- 🔴 **Rouge** = occupée / réservée
- 🟡 **Or** = sélectionnée (focus courant)

## Typographie

- Titres / numéros de table : sans-serif, **bold**, grande taille (ex. numéro `01` très lisible).
- Labels & tags : possibilité d'un style monospace pour les étiquettes techniques (repris du dossier).
- Corps : sans-serif lisible (system-ui / Inter).

## Composants récurrents

- **Carte table** : grand numéro centré, bordure colorée selon statut, coins arrondis.
- **Carte plat** : nom + stepper `−/+`, passe en vert quand quantité > 0.
- **Boutons d'action pleine largeur** : vert (valider/ajouter), rouge (annuler/clôturer).
- **En-tête** : logo FONTENAY (or) + navigation (Planning · Plan de Salle · Cuisine).
- **Allergies** : toujours mises en évidence (badge rouge/or) sur planning, commande et cuisine.

## Écrans (inventaire maquette)

1. **Site réservation** (web public) — formulaire sur panneau or, fond noir.
2. **App serveur PWA** — Login · Plan de salle · Détail table (+/✕) · Prise de commande · Résumé · Réservations.
3. **Écran cuisine** — cartes par table, plats à préparer, validation par item, allergies en évidence.
