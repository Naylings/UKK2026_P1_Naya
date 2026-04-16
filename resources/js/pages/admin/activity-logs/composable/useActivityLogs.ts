import { computed, reactive, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { useActivityLogStore } from "@/stores/activity-log";
import type { ActivityLogItem } from "@/types/activity-log";

export function useActivityLogs() {
  const activityLogStore = useActivityLogStore();
  const toast = useToast();

  const filters = reactive({
    search: "",
    module: "",
    action: "",
    page: 1,
    per_page: 10,
  });

  const loading = computed(() => activityLogStore.loading);
  const logs = computed(() => activityLogStore.logs);
  const meta = computed(() => ({
    current_page: activityLogStore.currentPage,
    last_page: activityLogStore.lastPage,
    total: activityLogStore.total,
    per_page: activityLogStore.perPage,
  }));

  async function loadLogs() {
    const ok = await activityLogStore.fetchLogs({
      search: filters.search || undefined,
      module: filters.module || undefined,
      action: filters.action || undefined,
      page: filters.page,
      per_page: filters.per_page,
    });

    if (!ok && activityLogStore.error) {
      toast.add({
        severity: "error",
        summary: "Gagal",
        detail: activityLogStore.error,
        life: 4000,
      });
    }
  }

  function onPageChange(event: { page: number; rows: number }) {
    filters.page = event.page + 1;
    filters.per_page = event.rows;
    loadLogs();
  }

  function clearFilter() {
    filters.search = "";
    filters.module = "";
    filters.action = "";
    filters.page = 1;
    loadLogs();
  }

  const showDetailModal = ref(false);
  const selectedLog = ref<ActivityLogItem | null>(null);

  async function openDetailModal(item: ActivityLogItem) {
    selectedLog.value = item;
    showDetailModal.value = true;

    const ok = await activityLogStore.fetchLogById(item.id);
    if (ok && activityLogStore.currentLog) {
      selectedLog.value = activityLogStore.currentLog;
    }
  }

  function closeDetailModal() {
    showDetailModal.value = false;
    selectedLog.value = null;
  }

  return {
    filters,
    loading,
    logs,
    meta,
    loadLogs,
    onPageChange,
    clearFilter,
    showDetailModal,
    selectedLog,
    openDetailModal,
    closeDetailModal,
  };
}
