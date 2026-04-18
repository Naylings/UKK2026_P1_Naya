import { ref } from "vue";
import { loanRequestService } from "@/services/loanService";
import type { LoanFilters, LoanResponse } from "@/types/loan";

export function useLoanList() {

  const loans = ref<LoanResponse[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

const filters = ref<LoanFilters>({
    status: "",
    search: "",
    page: 1,
    per_page: 10,
  });

  const meta = ref<any>(null);


async function loadMyLoans(params?: any) {
    loading.value = true;
    error.value = null;

    const apiParams = {
        status: filters.value.status || undefined,
        search: filters.value.search || undefined,
        page: filters.value.page,
        per_page: filters.value.per_page,
        ...params,
      };
    console.log('loadMyLoans params:', apiParams);

    try {
      const res = await loanRequestService.getMyLoans(apiParams);

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

  function setFilters(val: any) {
    filters.value = val;
    loadMyLoans();
  }

  function setSearch(val: string) {
    filters.value.search = val;
    filters.value.page = 1;
    loadMyLoans();
  }

  function clearFilter() {
    filters.value = {
      status: "",
      search: "",
      page: 1,
      per_page: 10,
    };

    loadMyLoans();
  }

  const detailLoan = ref<LoanResponse | null>(null);
  const showDetailModal = ref(false);

  function openDetailModal(loan: LoanResponse) {
    detailLoan.value = loan;
    showDetailModal.value = true;
  }

  function closeDetailModal() {
    detailLoan.value = null;
    showDetailModal.value = false;
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
    setFilters,
    setSearch,

    detailLoan,
    showDetailModal,
    openDetailModal,
    closeDetailModal,
  };
}
