<script setup lang="ts">
import { onBeforeMount } from "vue";
import { useLoanList } from "./composables/useLoanList";
import { useReturnLoan } from "./composables/useReturnLoan";
import ReturnLoanForm from "@/components/dialogs/forms/ReturnLoanForm.vue";
import LoanDetail from "@/components/dialogs/details/LoanDetail.vue";

const {
    loans,
    loading,
    filters,
    meta,
    loadMyLoans,
    onPageChange,
    clearFilter,
    showDetailModal,
    detailLoan,
    openDetailModal,
} = useLoanList();

const {
    isModalOpen,
    selectedLoan,
    loading :returnLoading,
    error,
    successMessage,

    openReturnModal,
    closeReturnModal,
    submitReturn,
} = useReturnLoan();

const statusOptions = [
    { label: "Semua Status", value: "" },
    { label: "Pending", value: "pending" },
    { label: "Approve", value: "approve" },
    { label: "Rejected", value: "rejected" },
    { label: "Expired", value: "expired" },
];

onBeforeMount(() => {
    loadMyLoans();
});
</script>

<template>
    <!-- TABLE -->
    <LoanTable
        :loans="loans"
        :loading="loading"
        :meta="meta"
        :filters="filters"
        :status-options="statusOptions"
        mode="user"
        @update:filters="
            (val) => {
                Object.assign(filters, val);
                loadMyLoans();
            }
        "
        @page-change="onPageChange"
        @search="
            (val) => {
                filters.search = val;
                filters.page = 1;
                loadMyLoans();
            }
        "
        @reset="clearFilter"
        @return="openReturnModal"
        @detail="openDetailModal"
    />

    <ReturnLoanForm
        v-model:visible="isModalOpen"
        :selected-loan="selectedLoan"
        :loading="returnLoading"
        @submit="submitReturn"
    />
    <LoanDetail v-model:visible="showDetailModal" :detail-loan="detailLoan" />
</template>
