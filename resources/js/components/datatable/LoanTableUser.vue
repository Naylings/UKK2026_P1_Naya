<script setup lang="ts">
import { ref, watch } from "vue";
import { useFormatter } from "@/utils/useFormatter";

const { formatDate } = useFormatter();

const props = defineProps<{
  loans: any[];
  loading: boolean;
  meta: any;
  filters: any;
  statusOptions: Array<{ label: string; value: string }>;
}>();

const emit = defineEmits<{
  (e: "update:filters", value: any): void;
  (e: "page-change", value: any): void;
  (e: "search", value: string): void;
  (e: "reset"): void;
  (e: "return", loan: any): void;
  (e: "detail", loan: any): void;
}>();

const globalFilter = ref("");

let timeout: any;
watch(globalFilter, (val) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    emit("search", val);
  }, 300);
});

function getStatusLabel(loan: any) {
  if (loan.status === "pending") return "Menunggu Persetujuan Pinjam";
  if (loan.status === "rejected") return "Pinjaman Ditolak";
  if (loan.status === "expired") return "Pinjaman Kadaluarsa";
  if (loan.status === "approved") return "Sedang Dipinjam";

  if (loan.status === "returned") {
    if (!loan.tool_return?.employee) {
      return "Menunggu Verifikasi Pengembalian";
    }
    return "Pengembalian Dikonfirmasi";
  }
  return loan.status;
}

function getStatusSeverity(loan: any) {
  if (loan.status === "pending") return "warn";
  if (loan.status === "rejected") return "danger";
  if (loan.status === "expired") return "secondary";
  if (loan.status === "approved") return "info";

  if (loan.status === "returned") {
    if (!loan.tool_return?.employee) {
      return "warn";
    }
    return "success";
  }
  return "info";
}

function getViolationTag(violation: any) {
  if (violation.status === "active") {
    return { label: "Ada Pelanggaran & Denda", severity: "danger" };
  }
  return { label: "Pelanggaran Diselesaikan", severity: "success" };
}
</script>

<template>
  <div class="card">
    <div class="font-semibold text-xl mb-4">Riwayat Peminjaman</div>

    <div class="flex flex-col md:flex-row gap-3 mb-5">
      <InputText v-model="globalFilter" placeholder="Cari..." />

      <Select
        :modelValue="filters.status"
        :options="statusOptions"
        optionLabel="label"
        optionValue="value"
        placeholder="Status"
        @update:modelValue="
          (val) => emit('update:filters', { ...filters, status: val, page: 1 })
        "
      />

      <Button label="Reset" outlined @click="$emit('reset')" />
    </div>

    <DataTable
      :value="loans"
      :loading="loading"
      paginator
      :rows="meta?.per_page || 10"
      :totalRecords="meta?.total || 0"
      :first="((meta?.current_page || 1) - 1) * (meta?.per_page || 10)"
      @page="(e) => $emit('page-change', e)"
    >
      <Column header="No">
        <template #body="{ index }">
          {{ (meta?.current_page - 1) * (meta?.per_page || 10) + index + 1 }}
        </template>
      </Column>

      <Column field="purpose" header="Tujuan" />

      <Column header="Alat">
        <template #body="{ data }">
          {{ data.tool?.name }}
        </template>
      </Column>

      <Column header="Unit">
        <template #body="{ data }">
          <Tag :value="data.unit?.code" severity="secondary" />
        </template>
      </Column>

      <Column header="Tanggal">
        <template #body="{ data }">
          <div class="text-sm">
            <span class="text-blue-600 font-medium">{{
              formatDate(data.loan_date)
            }}</span>
            <span class="mx-1 text-gray-400">→</span>
            <span class="text-red-600 font-medium">{{
              formatDate(data.due_date)
            }}</span>
          </div>
        </template>
      </Column>

      <Column header="Status">
        <template #body="{ data }">
          <div class="flex flex-col gap-1 items-start">
            <Tag
              :value="getStatusLabel(data)"
              :severity="getStatusSeverity(data)"
            />
            <Tag
              v-if="data.violation"
              :value="getViolationTag(data.violation).label"
              :severity="getViolationTag(data.violation).severity"
              icon="pi pi-exclamation-triangle"
            />
          </div>
        </template>
      </Column>

      <Column header="Aksi">
        <template #body="{ data }">
          <div class="flex gap-2">
            <Button
              v-if="data.status === 'approved' && !data.tool_return"
              icon="pi pi-replay"
              v-tooltip.top="'Kembalikan'"
              severity="warn"
              text
              @click="$emit('return', data)"
            />
            <Button
              icon="pi pi-eye"
              v-tooltip.top="'Detail'"
              text
              @click="$emit('detail', data)"
            />
          </div>
        </template>
      </Column>
    </DataTable>
  </div>
</template>
