import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
import { useToast } from "primevue/usetoast";
import { useAppConfigStore } from "@/stores/appconfig";
import { useUserStore } from "@/stores/user";
import { useDashboardStore } from "@/stores/dashboard";

export function useLogout() {
    const authStore = useAuthStore();
    const router = useRouter();
    const toast = useToast();
    const appConfigStore = useAppConfigStore();
    const userStore = useUserStore();
    const dashboardStore = useDashboardStore();

    const resetStores = () => {
        authStore.$reset();
        appConfigStore.$reset();
        userStore.$reset();
        dashboardStore.$reset();
    };

    const handleLogout = async () => {
        try {
            const message = await authStore.logout();

            resetStores();

            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: message,
                life: 3000,
            });

            await router.replace({ name: "login" });

        } catch (error) {
            toast.add({
                severity: "error",
                summary: "Gagal",
                detail:
                    error instanceof Error
                        ? error.message
                        : "Logout gagal",
            });
        }
    };

    return {
        handleLogout,
    };
}