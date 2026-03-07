import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

import Lara from '@primeuix/themes/Lara';
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';

// PrimeVue services & directives that still require explicit registration
import ToastService from 'primevue/toastservice';
import StyleClass from 'primevue/styleclass';

// CSS
import 'primeicons/primeicons.css';

import '@/assets/tailwind.css';
import '@/assets/styles.scss';
import { definePreset } from '@primeuix/themes';

// component that still error
import { OverlayBadge, Tooltip, TabPanels, Fluid, ToggleSwitch, Toast, Select, DatePicker, Popover, Drawer, Step, ConfirmPopup, StepList, Tab, TabList, Tabs, AccordionHeader, AccordionContent, AccordionPanel } from 'primevue';
// import Tooltip from 'primevue/tooltip';

const app = createApp(App);

const CustomLara = definePreset(Lara, {
    semantic: {
        primary: {
            50: '#fff7ed',
            100: '#ffedd5',
            200: '#fed7aa',
            300: '#fdba74',
            400: '#fb923c',
            500: '#f97316',
            600: '#ea580c',
            700: '#c2410c',
            800: '#9a3412',
            900: '#7c2d12',
            950: '#431407'
        },
        surface: {
            0: '#ffffff',
            50: '#fafaf9',
            100: '#f5f5f4',
            200: '#e7e5e4',
            300: '#d6d3d1',
            400: '#a8a29e',
            500: '#78716c',
            600: '#57534e',
            700: '#44403c',
            800: '#292524',
            900: '#1c1917',
            950: '#0c0a09'
        }
    }
});

app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: CustomLara,
        options: {
            darkModeSelector: '.app-dark'
        }
    }
});
app.use(ToastService);
app.use(ConfirmationService);

app.directive('styleclass', StyleClass);
app.directive('tooltip', Tooltip);


// component that still error
app.component('Fluid', Fluid);
app.component('TabPanels', TabPanels);
app.component('Toast', Toast);
app.component('Select', Select);
app.component('DatePicker', DatePicker);
app.component('ToggleSwitch', ToggleSwitch);
app.component('Popover', Popover);
app.component('Drawer', Drawer);
app.component('Tooltip', Tooltip);
app.component('OverlayBadge', OverlayBadge);
app.component('Step', Step);
app.component('ConfirmPopup', ConfirmPopup);
app.component('StepList', StepList);
app.component('Tab', Tab);
app.component('TabList', TabList);
app.component('Tabs', Tabs);
app.component('AccordionHeader', AccordionHeader);
app.component('AccordionContent', AccordionContent);
app.component('AccordionPanel', AccordionPanel);


app.mount('#app');
