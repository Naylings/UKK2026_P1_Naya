<script setup lang="ts">
import { onMounted } from "vue";
import { useViolation } from "./composable/useViolation";


const {
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
  showSettleModal,
  settleDescription,
  openSettleModal,
  submitSettlement,
} = useViolation();

const statusOptions = [
  { label: "Semua Status", value: "" },
  { label: "Aktif", value: "active" },
  { label: "Settled", value: "settled" },
];

const typeOptions = [
  { label: "Semua Tipe", value: "" },
  { label: "Late", value: "late" },
  { label: "Damaged", value: "damaged" },
  { label: "Lost", value: "lost" },
  { label: "Other", value: "other" },
];

onMounted(() => {
  loadViolations();
});
</script>

<template>
  <div class="space-y-6">
    <ViolationTable
      :violations="violations"
      :loading="loading"
      :meta="meta"
      :filters="filters"
      :status-options="statusOptions"
      :type-options="typeOptions"
      @update:filters="loadViolations"
      @page-change="onPageChange"
      @reset="clearFilter"
      @detail="openDetailModal"
    />

    <ViolationActionForm
      v-model:showDetail="showDetailModal"
      v-model:showSettle="showSettleModal"
      v-model:description="settleDescription"
      :selected="selectedViolation"
      :loading="loading"
      @settle="openSettleModal"
      @submit="submitSettlement"
    />
  </div>
</template>