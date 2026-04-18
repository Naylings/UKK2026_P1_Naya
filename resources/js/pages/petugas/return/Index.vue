<script setup lang="ts">
import { onMounted } from "vue";
import { useReturn } from "./composables/useReturn";
import ReturnTablePetugas from "@/components/datatable/ReturnTablePetugas.vue";
import ReturnReviewForm from "@/components/dialogs/forms/ReturnReviewForm.vue";
import LoanDetail from "@/components/dialogs/details/LoanDetail.vue";

const {
  filters,
  loading,
  returns,
  meta,
  loadReturns,
  onPageChange,
  clearFilter,

  showReviewModal,
  selectedReturn,
  openReviewModal,
  closeReviewModal,
  submitReview,
  config,

  detailLoan,
  showDetailModal,
  openDetailModal,
} = useReturn();

const statusOptions = [
  { label: "Menunggu Verifikasi", value: "false" },
  { label: "Sudah Diverifikasi", value: "true" },
  { label: "Semua", value: "" },
];

onMounted(() => {
  loadReturns();
});
</script>

<template>
  <div class="space-y-6">
    <ReturnTablePetugas
      :returns="returns"
      :loading="loading"
      :meta="meta"
      :filters="filters"
      :status-options="statusOptions"
      @update:filters="loadReturns"
      @page-change="onPageChange"
      @reset="clearFilter"
      @review="openReviewModal"
      @detail="openDetailModal"
    />

    <ReturnReviewForm
      v-model:visible="showReviewModal"
      :selected-return="selectedReturn"
      :loading="loading"
      :config="config"
      @submit="submitReview"
    />

    <LoanDetail v-model:visible="showDetailModal" :detail-loan="detailLoan" />
  </div>
</template>
