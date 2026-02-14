import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve, dirname } from 'path';
import { readFileSync, writeFileSync } from 'fs';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

/** Generate admin.asset.php for WordPress enqueue (deps + version). */
function wpAssetPlugin() {
  return {
    name: 'wp-asset-php',
    closeBundle() {
      const outDir = resolve(__dirname, 'assets/dist');
      let version = '1.0.0';
      try {
        const pkg = JSON.parse(readFileSync(resolve(__dirname, 'package.json'), 'utf-8'));
        version = pkg.version || version;
      } catch (_) {}
      const php = `<?php
return array(
  'dependencies' => array(),
  'version'      => '${version}',
);
`;
      writeFileSync(resolve(outDir, 'admin.asset.php'), php);
    },
  };
}

export default defineConfig({
  plugins: [vue(), wpAssetPlugin()],
  root: resolve(__dirname, 'admin'),
  publicDir: false,
  build: {
    outDir: resolve(__dirname, 'assets/dist'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: { admin: resolve(__dirname, 'admin/main.js') },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: (assetInfo) => {
          const name = assetInfo.name || '';
          return name.endsWith('.css') ? 'admin.css' : '[name].[ext]';
        },
      },
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'admin'),
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@use "@/assets/styles/variables.scss" as *;`,
      },
    },
  },
});
