import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { dashboardService } from "@/services/dashboardService";
import type {
  UserDashboardResponse,
  DashboardParams,
  Paginated,
  SimpleLoan,
  SimpleViolation,
} from "@/types/dashboard";

export const useDashboardStore = defineStore("dashboard-user", () => {
  const data = ref<UserDashboardResponse | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);


  const summary = computed(() => data.value?.summary ?? null);

  const alerts = computed(() => data.value?.alerts ?? {
    has_overdue: false,
    is_due_soon: false,
    has_active_violation: false,
  });

  const activeLoans = computed<Paginated<SimpleLoan>>(
    () =>
      data.value?.active_loans ?? {
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      }
  );

  const returnHistory = computed(
    () => data.value?.return_history ?? []
  );

  const violations = computed<Paginated<SimpleViolation>>(
    () =>
      data.value?.violations ?? {
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 5,
        total: 0,
      }
  );

  const settlements = computed(() => data.value?.settlements ?? []);
  const appeals = computed(() => data.value?.appeals ?? []);
  const recommendations = computed(() => data.value?.recommendations ?? []);
  const recentActivities = computed(
    () => data.value?.recent_activities ?? []
  );

  const hasData = computed(() => !!data.value);

  const isEmpty = computed(() => {
    if (!data.value) return true;

    return (
      activeLoans.value.data.length === 0 &&
      returnHistory.value.length === 0 &&
      violations.value.data.length === 0 &&
      settlements.value.length === 0 &&
      appeals.value.length === 0 &&
      recommendations.value.length === 0
    );
  });


  const isOverdue = (dueDate: string) =>
    new Date(dueDate) < new Date();

  const isNearDue = (dueDate: string) => {
    const due = new Date(dueDate);
    const now = new Date();
    const diffDays =
      (due.getTime() - now.getTime()) / (1000 * 60 * 60 * 24);

    return diffDays <= 3 && diffDays >= 0;
  };

  const hasAlerts = computed(() => {
    const a = alerts.value;
    return a.has_overdue || a.is_due_soon || a.has_active_violation;
  });


  async function fetchDashboard(
    filters?: DashboardParams
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      data.value = await dashboardService.getUserDashboard(filters);
      return true;
    } catch (err) {
      error.value =
        err instanceof Error ? err.message : "Terjadi kesalahan";
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function refetch(filters?: DashboardParams) {
    return fetchDashboard(filters);
  }

  function clearError() {
    error.value = null;
  }

  function reset() {
    data.value = null;
    loading.value = false;
    error.value = null;
  }

  async function fetchAdminDashboard(
    filters?: DashboardParams
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      data.value = await dashboardService.getAdminDashboard(filters) as any;
      return true;
    } catch (err) {
      error.value =
        err instanceof Error ? err.message : "Terjadi kesalahan";
      return false;
    } finally {
      loading.value = false;
    }
  }

  return {
    data,
    loading,
    error,

    summary,
    alerts,
    activeLoans,
    returnHistory,
    violations,
    settlements,
    appeals,
    recommendations,
    recentActivities,

    hasData,
    isEmpty,
    hasAlerts,

    isOverdue,
    isNearDue,

    fetchDashboard,
    refetch,
    clearError,
    reset,

    
    $reset() {
      data.value = null;
      loading.value = false;
      error.value = null;
    },

    fetchAdminDashboard,
  };
});