import { onMounted, computed, type Ref } from "vue";
import { useDashboardStore } from "@/stores/dashboard";
import type { DashboardParams } from "@/types/dashboard";

export function useUserDashboard(filters?: Ref<DashboardParams | null>) {
  const store = useDashboardStore();

  onMounted(() => {
    
    if (!store.hasData) {
      store.fetchDashboard(filters?.value || {});
    }
  });

  return {
    
    loading: computed(() => store.loading),
    error: computed(() => store.error),
    isEmpty: computed(() => store.isEmpty),

    
    summary: computed(() => store.summary),
    alerts: computed(() => store.alerts),
    
    
    activeLoans: computed(() => store.activeLoans || []),
    violations: computed(() => store.violations?.data || []),
    
    
    returnHistory: computed(() => store.returnHistory),
    settlements: computed(() => store.settlements),
    appeals: computed(() => store.appeals),
    recommendations: computed(() => store.recommendations),
    recentActivities: computed(() => store.recentActivities),

    
    isOverdue: store.isOverdue,
    isNearDue: store.isNearDue,
    refetch: () => store.refetch(filters?.value),
    reset: store.reset
  };
}