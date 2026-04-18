import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import Components from "unplugin-vue-components/vite";
import { PrimeVueResolver } from "@primevue/auto-import-resolver";
import tailwindcss from "@tailwindcss/vite";

import { fileURLToPath, URL } from "node:url";

export default defineConfig({
  plugins: [
    vue(),

    Components({
      dirs: ["resources/js/components"],
      extensions: ["vue"],
      deep: true,
      dts: "resources/js/components.d.ts", 
      resolvers: [PrimeVueResolver()],
    }),
    tailwindcss(),
    laravel({
      input: ["resources/js/app.ts"],
      refresh: true,
    }),
  ],
  resolve: {
    alias: {
      "@": "/resources/js",
    },
  },
  optimizeDeps: {
    include: ["dayjs"],
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: "modern-compiler",
      },
    },
  },
  server: {
    host: "localhost",
    port: 5173,
    strictPort: true,
  },
});
