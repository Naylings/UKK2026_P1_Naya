<script setup lang="ts">
import { ref, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import AppMenuItem from "./AppMenuItem.vue";
import { useLogout } from "./composables/logout";


const { handleLogout } = useLogout();
interface AppMenuLinkItem {
    label?: string;
    icon?: string;
    to?: string;
    items?: AppMenuLinkItem[];
    separator?: boolean;
    roles?: string[];
    visible?: boolean;
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
                roles: ["Admin"],
            },
            {
                label: "Kategori",
                icon: "pi pi-fw pi-tags",
                to: "/categories",
                roles: ["Admin"],
            },
            {
                label: "Alat",
                icon: "pi pi-fw pi-wrench",
                to: "/tools",
                roles: ["Admin"],
            },
            {
                label: "Konfigurasi",
                icon: "pi pi-fw pi-cog",
                to: "/app-config",
                roles: ["Admin"],
            },
            {
                label: "Activity Log",
                icon: "pi pi-fw pi-history",
                to: "/activity-logs",
                roles: ["Admin"],
            },
            {
                label: "Daftar Alat",
                icon: "pi pi-fw pi-box",
                to: "/tools/user",
                roles: ["User"],
            },
            {
                label: "Peminjaman",
                icon: "pi pi-fw pi-shopping-cart",
                to: "/loans",
                roles: ["User"],
            },
            {
                label: "Appeal",
                icon: "pi pi-fw pi-thumbs-up",
                to: "/appeals",
                roles: ["User"],
            },
            {
                label: "Pengajuan Peminjaman",
                icon: "pi pi-fw pi-send",
                to: "/staff/loans",
                roles: ["Employee"],
            },
            {
                label: "Appeal",
                icon: "pi pi-fw pi-thumbs-up",
                to: "/staff/appeals",
                roles: ["Admin"],
            },
            {
                label: "Pengembalian Barang",
                icon: "pi pi-fw pi-undo",
                to: "/staff/returns",
                roles: ["Employee"],
            },
            {
                label: "Pelanggaran",
                icon: "pi pi-fw pi-ban",
                to: "/staff/violations",
                roles: ["Employee"],
            },
            {
                label: "Buat Laporan",
                icon: "pi pi-fw pi-file-pdf",
                to: "/staff/reports",
                roles: ["Employee"],
            },
        ],
    },
]);

const filteredModel = computed(() => {
    return model.value.map(group => ({
        ...group,
        items: group.items?.filter(item => {
            if (!item.roles) return true;
            return item.roles.includes(authStore.user?.role || '');
        }) || [],
    }));
});


</script>


<template>
    <div class="flex flex-col h-full">
        <ul class="layout-menu flex-1">
            <template v-for="(item, i) in filteredModel" :key="item">
                <app-menu-item
                    v-if="!item.separator && item.items && item.items.length > 0"
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

