<script setup lang="ts">
import { onBeforeMount, ref } from "vue";
import { useAllLoansList } from "./composables/useAllLoansList";

const {
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
} = useAllLoansList();

const reviewNote = ref("");
const showValidation = ref(false);

const statusOptions = [
    { label: "Semua Status", value: "" },
    { label: "Pending", value: "pending" },
    { label: "Approve", value: "approve" },
    { label: "Rejected", value: "rejected" },
    { label: "Expired", value: "expired" },
];

onBeforeMount(() => {
    loadAllLoans();
});
</script>

<template>
    <LoanTable
        :loans="loans"
        :loading="loading"
        :meta="meta"
        :filters="filters"
        :status-options="statusOptions"
        mode="petugas"
        @update:filters="loadAllLoans"
        @page-change="onPageChange"
        @search="
            (val) => {
                filters.search = val;
                filters.page = 1;
                loadAllLoans();
            }
        "
        @reset="clearFilter"
        @review="openReviewModal"
        @detail="openDetailModal"
    />
    <LoanReviewForm
        v-model:visible="showReviewModal"
        :selected-loan="selectedLoan"
        :review-loading="reviewLoading"
        @approve="approveLoan"
        @reject="rejectLoan"
    />

    <LoanDetail v-model:visible="showDetailModal" :detail-loan="detailLoan" />
</template>
