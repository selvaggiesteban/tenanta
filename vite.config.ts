import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    vuetify({
      autoImport: true,
      styles: { configFile: 'resources/styles/settings.scss' },
    }),
    AutoImport({
      imports: ['vue', 'vue-router', 'pinia', '@vueuse/core'],
      vueTemplate: true,
      dts: 'resources/js/auto-imports.d.ts',
    }),
    Components({
      dirs: ['resources/js/components', 'resources/js/@core/components'],
      dts: 'resources/js/components.d.ts',
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@core': fileURLToPath(new URL('./resources/js/@core', import.meta.url)),
      '@layouts': fileURLToPath(new URL('./resources/js/@layouts', import.meta.url)),
      '@images': fileURLToPath(new URL('./resources/images', import.meta.url)),
      '@styles': fileURLToPath(new URL('./resources/styles', import.meta.url)),
    },
  },
  build: {
    chunkSizeWarningLimit: 5000,
  },
  server: {
    host: '0.0.0.0',
    hmr: {
      host: 'localhost',
    },
  },
})
