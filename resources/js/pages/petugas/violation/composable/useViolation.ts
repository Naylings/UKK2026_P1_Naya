import { computed, reactive, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { useViolationStore } from "@/stores/violation";
import { useSettlementStore } from "@/stores/settlement";
import type { Violation } from "@/types/violation";

export function useViolation() {
  const violationStore = useViolationStore();
  const settlementStore = useSettlementStore();
  const toast = useToast();

  const filters = reactive({
    search: "",
    status: "active",
    type: "",
    page: 1,
    per_page: 10,
  });

  const loading = computed(
    () => violationStore.loading || settlementStore.loading,
  );
  const violations = computed(() => violationStore.violations);
  const meta = computed(() => ({
    current_page: violationStore.currentPage,
    last_page: violationStore.lastPage,
    total: violationStore.total,
    per_page: violationStore.perPage,
  }));

  async function loadViolations() {
    const ok = await violationStore.fetchViolations({
      search: filters.search || undefined,
      status: filters.status || undefined,
      type: filters.type || undefined,
      page: filters.page,
      per_page: filters.per_page,
    });

    if (!ok && violationStore.error) {
      toast.add({
        severity: "error",
        summary: "Gagal",
        detail: violationStore.error,
        life: 4000,
      });
    }
  }

  function onPageChange(event: { page: number; rows: number }) {
    filters.page = event.page + 1;
    filters.per_page = event.rows;
    loadViolations();
  }

  function clearFilter() {
    filters.search = "";
    filters.status = "active";
    filters.type = "";
    filters.page = 1;
    loadViolations();
  }

  const showDetailModal = ref(false);
  const selectedViolation = ref<Violation | null>(null);

  async function openDetailModal(item: Violation) {
    selectedViolation.value = item;
    showDetailModal.value = true;

    const ok = await violationStore.fetchViolationById(item.id);
    if (ok && violationStore.currentViolation) {
      selectedViolation.value = violationStore.currentViolation;
    }
  }

  function closeDetailModal() {
    showDetailModal.value = false;
    selectedViolation.value = null;
  }

  const showSettleModal = ref(false);
  const settleDescription = ref("");

  function openSettleModal(item: Violation) {
    selectedViolation.value = item;
    settleDescription.value = "";
    showSettleModal.value = true;
  }

  function closeSettleModal() {
    showSettleModal.value = false;
    settleDescription.value = "";
  }

  async function submitSettlement() {
    if (!selectedViolation.value?.id) {
      return false;
    }

    if (!settleDescription.value.trim()) {
      toast.add({
        severity: "warn",
        summary: "Validasi",
        detail: "Deskripsi pelunasan wajib diisi.",
        life: 3000,
      });
      return false;
    }

    const ok = await settlementStore.settleViolation(
      selectedViolation.value.id,
      settleDescription.value.trim(),
    );

    if (ok) {
      toast.add({
        severity: "success",
        summary: "Berhasil",
        detail:
          settlementStore.successMessage ||
          "Pelanggaran berhasil diselesaikan.",
        life: 3000,
      });
      closeSettleModal();
      await openDetailModal(selectedViolation.value);
      await loadViolations();
      return true;
    }

    toast.add({
      severity: "error",
      summary: "Gagal",
      detail: settlementStore.error || "Gagal memproses pelunasan.",
      life: 5000,
    });

    return false;
  }

  return {
    filters,
    loading,
    violations,
    meta,
    loadViolations,
    onPageChange,
    clearFilter,
    showDetailModal,
    selectedViolation,
    openDetailModal,
    closeDetailModal,
    showSettleModal,
    settleDescription,
    openSettleModal,
    closeSettleModal,
    submitSettlement,
  };
}
