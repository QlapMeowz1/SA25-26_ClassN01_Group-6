import { defineConfig } from 'vite';
import path from 'path';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react(), tailwindcss()],
  publicDir: false,
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/react'),
    },
  },
  build: {
    manifest: 'manifest.json',
    outDir: 'public/build',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        'app': path.resolve(__dirname, 'resources/js/app.js'),
        'player-portal': path.resolve(__dirname, 'resources/react/main.tsx'),
      },
    },
  },
});
