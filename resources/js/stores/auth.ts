// ─────────────────────────────────────────────
// stores/auth.ts
// Pinia store untuk auth — state, getters, actions
// ─────────────────────────────────────────────

import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { authService } from "@/services/authService";
import type { AuthUser, LoginPayload } from "@/types/auth";
import { useAppConfigStore } from "./appconfig";

export const useAuthStore = defineStore("auth", () => {
    // ── State ──────────────────────────────────────────────────────────────

    const user = ref<AuthUser | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);
    const loginMessage = ref<string | null>(null);
    const logoutMessage = ref<string | null>(null);
    const appName = ref<string>("Peminjaman");

    /**
     * true  → fetchMe sudah pernah dipanggil (berhasil atau gagal)
     * false → belum pernah dicek (app baru buka)
     *
     * Dipakai di router guard untuk menghindari double-fetch.
     */
    const initialized = ref(false);

    // ── Getters ────────────────────────────────────────────────────────────

    const isLoggedIn = computed(() => !!user.value);
    const isAdmin = computed(() => user.value?.role === "Admin");
    const isEmployee = computed(() => user.value?.role === "Employee");

    // ── Actions ────────────────────────────────────────────────────────────

    /**
     * Dipanggil saat user submit form login.
     * Menyimpan user ke state dan mengembalikan true jika sukses.
     */
    async function login(payload: LoginPayload): Promise<boolean> {
        loading.value = true;
        error.value = null;

        try {
            const result = await authService.login(payload);
            user.value = result.user;
            loginMessage.value = result.message;
            return true;
        } catch (err) {
            // authService.login melempar string
            error.value = err as string;
            return false;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Dipanggil saat user klik tombol logout.
     * Selalu bersihkan state meskipun request BE gagal.
     * Mengembalikan pesan dari backend.
     */
    async function logout(): Promise<string> {
        loading.value = true;

        try {
            const message = await authService.logout();
            logoutMessage.value = message;
            return message;
        } finally {
            user.value = null;
            initialized.value = false;
            loading.value = false;
            error.value = null;
        }
    }

    /**
     * Dipanggil sekali saat app pertama buka (di router beforeEach atau App.vue).
     * Mengisi state user dari token yang ada di localStorage.
     */
    async function fetchMe(): Promise<void> {
        if (initialized.value) return;

        loading.value = true;

        try {
            const appConfigStore = useAppConfigStore();

            await Promise.allSettled([
                authService.me().then((result) => {
                    user.value = result;
                }),
                appConfigStore.fetchConfig(),
            ]);
        } finally {
            initialized.value = true;
            loading.value = false;
        }
    }

    /** Reset manual — misal saat BE kembalikan 401 via event global */
    function clearSession(): void {
        user.value = null;
        initialized.value = false;
        error.value = null;
    }

    /** Bersihkan error (dipanggil saat user mulai ketik lagi di form) */
    function clearError(): void {
        error.value = null;
    }

    return {
        // state
        user,
        loading,
        error,
        loginMessage,
        initialized,
        logoutMessage,
        // getters
        isLoggedIn,
        isAdmin,
        isEmployee,
        // actions
        login,
        logout,
        fetchMe,
        clearSession,
        clearError,
    };
});
