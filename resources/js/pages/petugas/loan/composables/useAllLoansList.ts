import { ref } from "vue";
import { loanRequestService } from "@/services/loanService";
import type { LoanResponse } from "@/types/loan";
import { useToast } from "primevue";

export function useAllLoansList() {
    const toast = useToast();
    // ── State ──────────────────────────────────────────────

    const loans = ref<LoanResponse[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const filters = ref({
        status: "",
        search: "",
        page: 1,
        per_page: 10,
    });

    const meta = ref<any>(null);

    const selectedLoan = ref<LoanResponse | null>(null);
    const showReviewModal = ref(false);

    const reviewLoading = ref(false);
    const showDetailModal = ref(false);
    const detailLoan = ref<LoanResponse | null>(null);

    // ── Actions ────────────────────────────────────────────

    async function loadAllLoans(params?: any) {
        loading.value = true;
        error.value = null;

        const apiParams = {
            status: filters.value.status || undefined,
            search: filters.value.search || undefined,
            page: filters.value.page,
            per_page: filters.value.per_page,
            ...params,
        };

        try {
            const res = await loanRequestService.getAllLoans(apiParams);

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
        loadAllLoans();
    }

    function clearFilter() {
        filters.value = {
            status: "",
            search: "",
            page: 1,
            per_page: 10,
        };

        loadAllLoans();
    }

    function openReviewModal(loan: LoanResponse) {
        selectedLoan.value = loan;
        showReviewModal.value = true;
    }

    function closeReviewModal() {
        selectedLoan.value = null;
        showReviewModal.value = false;
    }

    function openDetailModal(loan: LoanResponse) {
        detailLoan.value = loan;
        showDetailModal.value = true;
    }
    function closeDetailModal() {
        detailLoan.value = null;
        showDetailModal.value = false;
    }

    async function approveLoan(notes?: string) {
        if (!selectedLoan.value) return;

        reviewLoading.value = true;

        try {
            const res = await loanRequestService.approveLoan(
                selectedLoan.value.id,
                {
                    notes: notes?.trim() || null, // optional
                },
            );

            updateLoanInList(res.data);
            closeReviewModal();
        } finally {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: "Peminjaman disetujui.",
                life: 3000,
            });
            reviewLoading.value = false;
        }
    }

    async function rejectLoan(notes?: string) {
        if (!selectedLoan.value) return;

        const cleanNote = notes?.trim();

        if (!cleanNote) {
            toast.add({
                severity: "warn",
                summary: "Catatan wajib",
                detail: "Catatan wajib diisi untuk menolak peminjaman.",
                life: 3000,
            });
            return;
        }

        reviewLoading.value = true;

        try {
            const res = await loanRequestService.rejectLoan(
                selectedLoan.value.id,
                { notes: cleanNote },
            );

            updateLoanInList(res.data);
            closeReviewModal();

            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: "Peminjaman berhasil ditolak.",
                life: 3000,
            });
        } finally {
            reviewLoading.value = false;
        }
    }

    // helper
    function updateLoanInList(updated: LoanResponse) {
        const index = loans.value.findIndex((l) => l.id === updated.id);
        if (index !== -1) {
            loans.value[index] = updated;
        }
    }

    return {
        loans,
        loading,
        error,
        filters,
        meta,

        selectedLoan,
        showReviewModal,
        reviewLoading,

        detailLoan,
        showDetailModal,

        loadAllLoans,
        onPageChange,
        clearFilter,

        openReviewModal,
        closeReviewModal,

        openDetailModal,
        closeDetailModal,

        approveLoan,
        rejectLoan,
    };
}
