<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import AppMenuItem from "./AppMenuItem.vue";

interface AppMenuLinkItem {
    label?: string;
    icon?: string;
    to?: string;
    items?: AppMenuLinkItem[];
    separator?: boolean;
}

const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();

const model = ref<AppMenuLinkItem[]>([
    {
        label: "Home",
        items: [
            {
                label: "Dashboard",
                icon: "pi pi-fw pi-home",
                to: "/",
            },
            {
                label: "Users",
                icon: "pi pi-fw pi-users",
                to: "/users",
            },
            {
                label: "Kategori",
                icon: "pi pi-fw pi-tags",
                to: "/categories",
            },
            {
                label: "Alat",
                icon: "pi pi-fw pi-wrench",
                to: "/tools",
            },
            {
                label: "Konfigurasi",
                icon: "pi pi-fw pi-cog",
                to: "/app-config",
            },
            {
                label: "Activity Log",
                icon: "pi pi-fw pi-history",
                to: "/activity-logs",
            },
            {
                label: "Daftar Alat",
                icon: "pi pi-fw pi-wrench",
                to: "/tools/user",
            },
            {
                label: "Peminjaman",
                icon: "pi pi-fw pi-list",
                to: "/loans",
            },
            {
                label: "Pengajuan Peminjaman",
                icon: "pi pi-fw pi-list",
                to: "/staff/loans",
            },
            {
                label: "Pengembalian Barang",
                icon: "pi pi-fw pi-list",
                to: "/staff/returns",
            },
            {
                label: "Pelanggaran",
                icon: "pi pi-fw pi-list",
                to: "/staff/violations",
            },
            
        ],
    },
]);

async function handleLogout() {
    try {
        const message = await authStore.logout();

        toast.add({
            severity: "success",
            summary: "Berhasil",
            detail: message,
            life: 3000,
        });

        setTimeout(() => {
            router.push({ name: "login" });
        }, 500);
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Gagal",
            detail: error instanceof Error ? error.message : "Logout gagal",
        });
    }
}
</script>

<template>
    <div class="flex flex-col h-full">
        <ul class="layout-menu flex-1">
            <template v-for="(item, i) in model" :key="item">
                <app-menu-item
                    v-if="!item.separator"
                    :item="item"
                    :index="i"
                ></app-menu-item>
                <li v-if="item.separator" class="menu-separator"></li>
            </template>
        </ul>

        <div class="border-t border-surface-200 dark:border-surface-700 p-4">
            <button
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-4 py-2 rounded text-surface-900 dark:text-surface-0 hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
            >
                <i class="pi pi-sign-out text-lg"></i>
                <span>Logout</span>
            </button>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
