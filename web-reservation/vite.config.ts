import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { fileURLToPath, URL } from 'node:url'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
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
