import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import Components from "unplugin-vue-components/vite";
import { PrimeVueResolver } from "unplugin-vue-components/resolvers";
import tailwindcss from "@tailwindcss/vite";

import { fileURLToPath, URL } from "node:url";

export default defineConfig({
  plugins: [
    tailwindcss(),
    Components({
      resolvers: [PrimeVueResolver()],
      dts: "resources/js/components.d.ts", // optional
    }),
    laravel({
      input: ["resources/js/app.js"],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: {
      "@": "/resources/js",
    },
  },
  optimizeDeps: {
    noDiscovery: true,
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
    strictPort: true
  }
});
