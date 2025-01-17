import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import vueDevTools from 'vite-plugin-vue-devtools'
import basicSsl from '@vitejs/plugin-basic-ssl'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), vueJsx(), vueDevTools(), basicSsl()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@heroicons/vue': fileURLToPath(new URL('./node_modules/@heroicons/vue', import.meta.url)), // Add Heroicons alias
    },
  },
  server: {
    port: 8081, // Set your desired port
    https: true, // Retain the HTTPS configuration
  },
})
