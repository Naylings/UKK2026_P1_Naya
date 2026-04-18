<script setup lang="ts">
import { onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import router from "@/router/index";

const authStore = useAuthStore();

onMounted(() => authStore.fetchMe());

window.addEventListener("auth:unauthenticated", () => {
    authStore.clearSession();
    router.push({ name: "login" });
});
</script>

<template>
    <router-view />
    <Toast />
    <ConfirmDialog />
</template>

<style scoped></style>
