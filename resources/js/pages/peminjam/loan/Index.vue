<script setup lang="ts">
import { onBeforeMount } from "vue";
import { useLoanList } from "./composables/useLoanList";
import { useReturnLoan } from "./composables/useReturnLoan";

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
  loading: returnLoading,
  error,
  successMessage,

  openReturnModal,
  closeReturnModal,
  submitReturn: originalSubmitReturn,
} = useReturnLoan();

const handleSubmitReturn = async (payload: any) => {
  await originalSubmitReturn(payload);
  loadMyLoans();
};

const statusOptions = [
  { label: "Semua Status", value: "" },
  { label: "Pending", value: "pending" },
  { label: "Approved", value: "approve" },
  { label: "Rejected", value: "rejected" },
    { label: "Expired", value: "expired" },
  
];

onBeforeMount(() => {
  loadMyLoans();
});
</script>

<template>
  <LoanTableUser
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
    @submit="handleSubmitReturn"
  />
  <LoanDetail v-model:visible="showDetailModal" :detail-loan="detailLoan" />
</template>
