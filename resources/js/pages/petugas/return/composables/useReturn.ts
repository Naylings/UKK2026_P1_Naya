import { ref, reactive, computed } from "vue";
import { useReturnStore } from "@/stores/return";
import { useAppConfigStore } from "@/stores/appconfig";
import type { ReturnResponse, ReviewReturnPayload } from "@/types/return";
import { useToast } from "primevue/usetoast";

export function useReturn() {
  const returnStore = useReturnStore();
  const configStore = useAppConfigStore();
  const toast = useToast();


  const filters = reactive({
    reviewed: "",
    search: "",
    page: 1,
    per_page: 10,
  });

  const loading = computed(() => returnStore.loading);
  const returns = computed(() => returnStore.returns);
  const meta = computed(() => returnStore.meta);

  async function loadReturns() {
    if (!configStore.isLoaded) {
      await configStore.fetchConfig();
    }

    await returnStore.fetchReturns({
      reviewed: filters.reviewed || undefined,
      search: filters.search || undefined,
      page: filters.page,
      per_page: filters.per_page,
    });
  }

  function onPageChange(event: any) {
    filters.page = event.page + 1;
    filters.per_page = event.rows;
    loadReturns();
  }

  function clearFilter() {
    filters.reviewed = "";
    filters.search = "";
    filters.page = 1;
    loadReturns();
  }


  const showReviewModal = ref(false);
  const selectedReturn = ref<ReturnResponse | null>(null);
  const reviewForm = reactive<ReviewReturnPayload>({
    condition: "good",
    condition_notes: "",
    violation_type: null,
    total_score: 0,
    fine: 0,
    description: "",
  });

  function openReviewModal(item: ReturnResponse) {
    selectedReturn.value = item;
    reviewForm.condition = "good";
    reviewForm.condition_notes = "";
    reviewForm.violation_type = null;
    reviewForm.total_score = 0;
    reviewForm.fine = 0;
    reviewForm.description = "";

    showReviewModal.value = true;
  }
  function closeReviewModal() {
    showReviewModal.value = false;
    selectedReturn.value = null;
  }


  function calculateDefaultFine(type: string) {
    if (!selectedReturn.value?.loan?.tool?.price || !configStore.config) return;

    const price = selectedReturn.value.loan.tool.price;
    const config = configStore.config;

    let percentage = 0;
    let points = 0;

    if (type === "late") {
      percentage = config.late_fine;
      points = config.late_point;
    } else if (type === "damaged") {
      percentage = config.broken_fine;
      points = config.broken_point;
    } else if (type === "lost") {
      percentage = config.lost_fine;
      points = config.lost_point;
    }

    reviewForm.fine = (price * percentage) / 100;
    reviewForm.total_score = points;
  }


  async function submitReview(payload: ReviewReturnPayload) {
    if (!selectedReturn.value?.loan?.id) return;

    const success = await returnStore.confirmReturn(
      selectedReturn.value.loan.id,
      { ...payload },
    );

    if (success) {
      toast.add({
        severity: "success",
        summary: "Berhasil",
        detail: "Pengembalian telah diverifikasi",
        life: 3000,
      });
      closeReviewModal();
      loadReturns();
    } else {
      toast.add({
        severity: "error",
        summary: "Gagal",
        detail: returnStore.error || "Gagal memproses verifikasi",
        life: 5000,
      });
    }
  }

  const detailLoan = ref<any>(null);
  const showDetailModal = ref(false);

  function openDetailModal(item: ReturnResponse) {
    detailLoan.value = item.loan;
    showDetailModal.value = true;
  }

  function closeDetailModal() {
    showDetailModal.value = false;
    detailLoan.value = null;
  }

  return {
    filters,
    loading,
    returns,
    meta,
    loadReturns,
    onPageChange,
    clearFilter,

    showReviewModal,
    selectedReturn,
    reviewForm,
    openReviewModal,
    closeReviewModal,
    calculateDefaultFine,
    submitReview,

    detailLoan,
    showDetailModal,
    openDetailModal,
    closeDetailModal,

    config: computed(() => configStore.config),
  };
}
