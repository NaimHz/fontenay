# Fontenay Restaurants — Solution de gestion de table et de service

Prototype (MVP) en réponse à l'appel d'offre **AO-2026-FR-001**.

Couvre le parcours essentiel de bout en bout : un client **réserve en ligne**, le
service **visualise sa salle** et **prend la commande à table**, la **cuisine**
la reçoit en direct.

## Démo en ligne

Application staff hébergée sur une instance **AWS EC2** : **https://staff.tombenat.fr/**

## Équipe

Étudiants **M1 Développement** à **My Digital School** :

- **Shannon Besson**
- **Tom Benat**
- **Naïm Hamza-Zerigat**

## Architecture

```
┌─────────────────────┐     ┌─────────────────────┐
│  web-reservation     │     │  app-staff (PWA)     │
│  React · site public │     │  React · salle+cuisine│
└──────────┬───────────┘     └──────────┬───────────┘
           │            REST / JWT       │
           └───────────────┬─────────────┘
                  ┌─────────▼──────────┐
                  │  api (Symfony)     │
                  │  logique métier    │
                  └─────────┬──────────┘
                  ┌─────────▼──────────┐
                  │  PostgreSQL        │
                  └────────────────────┘
```

Toutes les briques sont **libres et auto-hébergeables** — aucune donnée confiée à
un service tiers (souveraineté).

**Temps réel.** Le plan de salle et l'écran cuisine se rafraîchissent automatiquement :
l'interface réinterroge l'API toutes les 2–3 s pour afficher les changements (nouvelles
commandes, changements d'état des tables).

| Dossier            | Rôle                                   | Stack                     |
|--------------------|----------------------------------------|---------------------------|
| `api/`             | Cœur applicatif, API REST, auth JWT    | Symfony 7 · PHP 8.4       |
| `web-reservation/` | Site public de réservation             | React · Vite              |
| `app-staff/`       | App serveurs + cuisine (installable)   | React · Vite · PWA        |
| `packages/ui/`     | Design system partagé (thème, classes) | CSS                       |
| `design/`          | Direction artistique (extraite du Figma) | —                       |

## Prérequis

- Docker + Docker Compose
- PHP 8.4+ et Composer
- Node.js 18+ et npm

## Installation & lancement

```bash
# 1. Base de données (PostgreSQL en conteneur)
docker compose up -d

# 2. API — http://localhost:8000
cd api
composer install
php bin/console lexik:jwt:generate-keypair   # clés JWT (locales, non versionnées)
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n     # jeux de données + comptes de test
symfony serve -d

# 3. Site de réservation — http://localhost:5173
cd ../web-reservation && npm install && npm run dev

# 4. App staff (PWA) — http://localhost:5174
cd ../app-staff && npm install && npm run dev
```

> **Port de l'API.** Les fronts appellent `http://localhost:8000` par défaut. Si l'API
> tourne ailleurs, lancez les fronts avec la variable `VITE_API_URL`, par ex. :
> `VITE_API_URL=http://localhost:8001 npm run dev`.

**Documentation de l'API** (Swagger/OpenAPI) : http://localhost:8000/api/doc

**Installation en PWA** : ouvrez l'app staff dans Chrome → « Installer l'application ».
La coquille reste disponible hors-ligne (mode dégradé).

## Comptes de test

Authentification par JWT (`POST /api/login` avec `{ email, password }`).
Mot de passe identique pour tous les comptes de démonstration : **`password`**.

| Email | Rôle | Établissement |
|-------|------|---------------|
| `owner@fontenay.fr` | Propriétaire | Tous |
| `maitre@clos.fr` | Maître d'hôtel | Le Clos Fontenay |
| `serveur@clos.fr` | Serveur | Le Clos Fontenay |
| `cuisine@clos.fr` | Cuisinier | Le Clos Fontenay |
| `maitre@cellier.fr` | Maître d'hôtel | Le Cellier Fontenay |
| `serveur@cellier.fr` | Serveur | Le Cellier Fontenay |

## Parcours de démonstration

1. **Réservation (Module A)** — sur le site (`:5173`), choisir un établissement, une
   date et un service, vérifier la disponibilité, saisir ses allergies, confirmer.
2. **Plan de salle (Module B)** — se connecter à l'app staff (`:5174`) avec
   `serveur@clos.fr`. Le plan de salle affiche les tables (vert = libre, rouge =
   occupée), et le planning du jour avec les allergies. Installer un client à une table.
3. **Prise de commande (Module C)** — taper une table, choisir des plats par catégorie,
   envoyer en cuisine.
4. **Cuisine (Module D)** — onglet « Cuisine » (ou compte `cuisine@clos.fr`) : les
   commandes arrivent en direct, allergies en évidence ; valider les plats (en
   préparation → servi).

## Tests & intégration continue

```bash
cd api && php bin/phpunit          # tests API (PHPUnit)
cd web-reservation && npm test     # tests front (Vitest) — idem app-staff
```

Une CI GitHub Actions (`.github/workflows/ci.yml`) exécute, à chaque push, les tests et
le build des trois briques.

## Périmètre du prototype

✅ Réservation en ligne · Plan de salle temps réel · Prise de commande · Écran cuisine
🕓 Spécifiés & chiffrés, livrés ensuite : emails, statistiques, administration complète.
