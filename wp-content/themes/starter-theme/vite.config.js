import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';
import path from 'path';

export default defineConfig({
  plugins: [liveReload([__dirname + '/**/*.php', __dirname + '/views/**/*.twig'])],

  // Dossier des assets statiques (fonts, images, videos)
  // Vite les sert automatiquement en dev et les copie dans dist/ au build
  publicDir: 'static',

  // Utiliser sass-embedded pour éviter le warning legacy-js-api
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
      },
    },
  },

  // Configuration pour MAMP virtual host
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    cors: true,
    hmr: {
      protocol: 'ws',
      host: 'localhost',
      port: 5173,
    },
    watch: {
      usePolling: true,
      interval: 100,
    },
  },

  // Point d'entrée
  build: {
    manifest: true,
    outDir: 'dist',
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'src/js/app.js'),
      },
      output: {
        entryFileNames: 'js/[name].[hash].js',
        chunkFileNames: 'js/[name].[hash].js',
        assetFileNames: assetInfo => {
          if (assetInfo.name.endsWith('.css')) {
            return 'css/[name].[hash].css';
          }
          // Fonts et autres assets restent dans fonts/
          if (assetInfo.name.match(/\.(woff|woff2|eot|ttf|otf)$/)) {
            return 'fonts/[name].[hash][extname]';
          }
          return 'assets/[name].[hash][extname]';
        },
      },
    },
    // Customize how URLs are generated - use relative paths in production
    experimental: {
      renderBuiltUrl(filename, { hostType }) {
        if (hostType === 'css' && filename.includes('/fonts/')) {
          // In CSS, use relative paths for fonts
          return { relative: true };
        }
      },
    },
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
      '@scss': path.resolve(__dirname, './src/scss'),
      '@js': path.resolve(__dirname, './src/js'),
      '@assets': path.resolve(__dirname, './assets'),
    },
  },
});
