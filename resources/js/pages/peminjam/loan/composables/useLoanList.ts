import { ref } from "vue";
import { loanRequestService } from "@/services/loanService";
import type { LoanResponse } from "@/types/loan";

export function useLoanList() {
  // ── State ──────────────────────────────────────────────

  const loans = ref<LoanResponse[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const filters = ref({
    status: "",
    page: 1,
    per_page: 10,
  });

  const meta = ref<any>(null);

  // ── Actions ────────────────────────────────────────────

  async function loadMyLoans(params?: any) {
    loading.value = true;
    error.value = null;

    try {
      const res = await loanRequestService.getMyLoans({
        status: filters.value.status || undefined,
        page: filters.value.page,
        per_page: filters.value.per_page,
        ...params,
      });

      loans.value = res.data;
      meta.value = res.meta;
    } catch (err: any) {
      error.value = err;
    } finally {
      loading.value = false;
    }
  }

  function onPageChange(event: any) {
    filters.value.page = event.page + 1;
    filters.value.per_page = event.rows;

    loadMyLoans();
  }

  function clearFilter() {
    filters.value = {
      status: "",
      page: 1,
      per_page: 10,
    };

    loadMyLoans();
  }

  return {
    loans,
    loading,
    error,
    filters,
    meta,

    loadMyLoans,
    onPageChange,
    clearFilter,
  };
}