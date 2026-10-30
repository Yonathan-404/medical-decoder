import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  esbuild: {
    include: [/\.jsx?$/],
    exclude: [],
    loader: 'jsx',
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost/backend/api',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '')
      }
    }
  }
})