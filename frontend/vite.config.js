import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@com': fileURLToPath(new URL('./src/components', import.meta.url)),
      '@pg': fileURLToPath(new URL('./src/pages', import.meta.url)),
      '@func': fileURLToPath(new URL('./src/components/functions', import.meta.url)),
      '@assets': fileURLToPath(new URL('./src/assets', import.meta.url)),
    },
  },

  server: {
    host: '0.0.0.0',
    port: process.env.VITE_HMR_PORT || 5173,
    hmr: {
      protocol: 'ws',
      port: process.env.VITE_HMR_PORT || 5173,
    },
    watch: {
      usePolling: true,
      useFsEvents: true,
      interval: 1000,
    }
  }
})
