import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

import Lara from "@primeuix/themes/lara";
import PrimeVue from "primevue/config";
import ConfirmationService from "primevue/confirmationservice";

import ToastService from "primevue/toastservice";
import StyleClass from "primevue/styleclass";

import "primeicons/primeicons.css";

import "@/assets/tailwind.css";
import "@/assets/styles.scss";
import { definePreset } from "@primeuix/themes";


const app = createApp(App);

import { createPinia } from "pinia";

const CustomLara = definePreset(Lara, {
  semantic: {
    primary: {
      50: "#fff7ed",
      100: "#ffedd5",
      200: "#fed7aa",
      300: "#fdba74",
      400: "#fb923c",
      500: "#f97316",
      600: "#ea580c",
      700: "#c2410c",
      800: "#9a3412",
      900: "#7c2d12",
      950: "#431407",
    },
    surface: {
      0: "#ffffff",
      50: "#fafaf9",
      100: "#f5f5f4",
      200: "#e7e5e4",
      300: "#d6d3d1",
      400: "#a8a29e",
      500: "#78716c",
      600: "#57534e",
      700: "#44403c",
      800: "#292524",
      900: "#1c1917",
      950: "#0c0a09",
    },
  },
});

const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(PrimeVue, {
  theme: {
    preset: CustomLara,
    options: {
      darkModeSelector: ".app-dark",
    },
  },
});
app.use(ToastService);
app.use(ConfirmationService);

app.directive("styleclass", StyleClass);




app.mount("#app");
