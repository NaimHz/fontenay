# Fontenay Restaurants — Solution de gestion de table et de service

Prototype (MVP) en réponse à l'appel d'offre **AO-2026-FR-001**.

Couvre le parcours essentiel de bout en bout : un client **réserve en ligne**, le
service **visualise sa salle** et **prend la commande à table**, la **cuisine**
la reçoit en direct.

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
| `api/`             | Cœur applicatif, API REST, auth JWT    | Symfony 7 · PHP 8.x       |
| `web-reservation/` | Site public de réservation             | React · Vite              |
| `app-staff/`       | App serveurs + cuisine (installable)   | React · Vite · PWA        |
| `design/`          | Design system (DA extraite du Figma)   | —                         |

## Prérequis

- Docker + Docker Compose
- PHP 8.2+ et Composer
- Node.js 18+ et npm

## Installation & lancement

> Détaillé au fur et à mesure que les briques sont livrées. Procédure cible :

```bash
# 1. Infra (PostgreSQL + Mercure)
docker compose up -d

# 2. API
cd api && composer install
php bin/console lexik:jwt:generate-keypair      # clés JWT (locales, non versionnées)
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n       # jeux de données + comptes de test
symfony serve -d                                # http://localhost:8000

# 3. Site de réservation
cd ../web-reservation && npm install && npm run dev   # http://localhost:5173

# 4. App staff (PWA)
cd ../app-staff && npm install && npm run dev          # http://localhost:5174
```

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

## Périmètre du prototype

✅ Réservation en ligne · Plan de salle temps réel · Prise de commande · Écran cuisine
🕓 Spécifiés & chiffrés, livrés ensuite : emails, statistiques, administration complète.
