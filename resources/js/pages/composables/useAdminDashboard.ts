import { onMounted, computed, type Ref } from "vue";
import { useDashboardStore } from "@/stores/dashboard";
import type { DashboardParams } from "@/types/dashboard";

export function useAdminDashboard(filters?: Ref<DashboardParams | null>) {
  const store = useDashboardStore();

  onMounted(() => {
    if (!store.hasData) {
      store.fetchAdminDashboard?.(filters?.value || {});
    }
  });

  return {
    loading: computed(() => store.loading),
    error: computed(() => store.error),

    summary: computed(() => (store.data as any)?.summary ?? null),
    alerts: computed(() => (store.data as any)?.alerts ?? null),
    stats: computed(() => (store.data as any)?.stats ?? null),

    pendingLoans: computed(() => (store.data as any)?.pending?.loans ?? []),
    pendingReturns: computed(() => (store.data as any)?.pending?.returns ?? []),
    pendingViolations: computed(() => (store.data as any)?.pending?.violations ?? []),

    recentActivities: computed(() => (store.data as any)?.recent_activities ?? []),

    refetch: () => store.fetchAdminDashboard?.(filters?.value),
    reset: store.reset,
  };
}