# Étape 1 — build des deux fronts React (vite build → dist/).
# VITE_API_URL est injectée au build (les variables Vite sont figées à la compilation).
FROM node:22-alpine AS front
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}
WORKDIR /build
# packages/ui est référencé par l'alias @ui des deux vite.config.ts (chemin relatif ../packages/ui)
COPY packages ./packages
COPY web-reservation ./web-reservation
COPY app-staff ./app-staff
RUN cd web-reservation && npm ci && npm run build \
 && cd ../app-staff && npm ci && npm run build

# Étape 2 — Caddy sert les fronts en statique et proxifie l'API.
FROM caddy:2-alpine
COPY deploy/Caddyfile /etc/caddy/Caddyfile
COPY --from=front /build/web-reservation/dist /srv/resa
COPY --from=front /build/app-staff/dist /srv/staff
# Nécessaire à php_fastcgi pour router vers index.php (les assets bundles/ restent dans le conteneur api)
COPY api/public /srv/api/public
