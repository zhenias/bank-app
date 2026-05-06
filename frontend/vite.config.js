import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    port: 5174,
    strictPort: true,
    // ✅ Powiedz Vite, że jest za proxy
    hmr: {
      host: 'localhost',    // przeglądarka łączy się z localhost:80
      port: 80,             // przez nginx!
      protocol: 'ws',
      clientPort: 80,       // mów klientowi żeby używał portu 80
    },
    watch: {
      usePolling: true
    },
    // ✅ CORS dla dev
    cors: {
      origin: 'http://localhost',
      credentials: true,
    }
  }
})