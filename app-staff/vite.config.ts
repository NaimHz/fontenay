import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { VitePWA } from 'vite-plugin-pwa'
import { fileURLToPath, URL } from 'node:url'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    // Application installable + coquille disponible hors-ligne (mode dégradé).
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['pwa-192.png', 'pwa-512.png'],
      manifest: {
        name: 'Fontenay — Service',
        short_name: 'Fontenay',
        description: 'Application de service et de cuisine — Fontenay Restaurants',
        lang: 'fr',
        theme_color: '#0d0d0d',
        background_color: '#0d0d0d',
        display: 'standalone',
        icons: [
          { src: 'pwa-192.png', sizes: '192x192', type: 'image/png' },
          { src: 'pwa-512.png', sizes: '512x512', type: 'image/png' },
          { src: 'pwa-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
        ],
      },
    }),
  ],
  resolve: {
    // Design system partagé (source unique : packages/ui)
    alias: {
      '@ui': fileURLToPath(new URL('../packages/ui', import.meta.url)),
    },
  },
  server: {
    // Autorise l'import de packages/ui situé hors du dossier de l'app (dev).
    fs: { allow: [fileURLToPath(new URL('..', import.meta.url))] },
  },
})
