import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { activityLogService } from "@/services/activityLogService";
import type { ActivityLogItem } from "@/types/activity-log";

export const useActivityLogStore = defineStore("activity-log", () => {
  const logs = ref<ActivityLogItem[]>([]);
  const currentLog = ref<ActivityLogItem | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  const logCount = computed(() => logs.value.length);

  async function fetchLogs(params?: {
    search?: string;
    module?: string;
    action?: string;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await activityLogService.getAll(params);
      logs.value = res.data;
      currentPage.value = res.meta.current_page;
      lastPage.value = res.meta.last_page;
      total.value = res.meta.total;
      perPage.value = res.meta.per_page;
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function fetchLogById(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      currentLog.value = await activityLogService.getById(id);
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  return {
    logs,
    currentLog,
    loading,
    error,
    currentPage,
    lastPage,
    total,
    perPage,
    logCount,
    fetchLogs,
    fetchLogById,
  };
});
