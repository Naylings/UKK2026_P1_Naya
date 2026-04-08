<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import router from '@/router/index';
import { Toast } from 'primevue';
 
const authStore = useAuthStore();
 
// Cek session satu kali saat app mount
onMounted(() => authStore.fetchMe());
 
// Dengarkan event global 401 dari axios interceptor
window.addEventListener('auth:unauthenticated', () => {
  authStore.clearSession();
  router.push({ name: 'login' });
});
</script>

<template>
    <router-view />
    <Toast />
</template>

<style scoped></style>
