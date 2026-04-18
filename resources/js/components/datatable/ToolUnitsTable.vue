<script setup lang="ts">
import { ref, watch } from "vue";
import type { ToolUnit } from "@/types/toolunit";

interface Props {
  units: ToolUnit[];
  loading?: boolean;
  currentPage?: number;
  lastPage?: number;
  total?: number;
  perPage?: number;
  filters?: { status: string };
}

interface Emits {
  (e: "create"): void;
  (e: "view-detail", unit: ToolUnit): void;
  (e: "delete", unit: ToolUnit): void;
  (e: "record-condition", unit: ToolUnit): void;
  (e: "update:filters", filters: { status: string }): void;
  (e: "page-change", event: { page: number; rows: number }): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 10,
  filters: () => ({ status: "" }),
});

const emit = defineEmits<Emits>();


const selectedRows = ref<ToolUnit[]>([]);
const statusFilter = ref<string>(props.filters?.status ?? "");


const statusMap: Record<string, { label: string; severity: string }> = {
  available: { label: "Tersedia", severity: "success" },
  lent: { label: "Dipinjam", severity: "warning" },
  nonactive: { label: "Nonaktif", severity: "danger" },
};

const conditionMap: Record<string, { label: string; severity: string }> = {
  good: { label: "Baik", severity: "success" },
  maintenance: { label: "Maintenance", severity: "warning" },
  broken: { label: "Rusak", severity: "danger" },
};


watch(statusFilter, (val) => {
  emit("update:filters", { status: val });
});

watch(
  () => props.filters,
  (val) => {
    statusFilter.value = val?.status ?? "";
  },
  { deep: true },
);


function onPageChange(event: any) {
  emit("page-change", { page: event.page + 1, rows: event.rows });
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function handleViewDetail(unit: ToolUnit) {
  emit("view-detail", unit);
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <Button
        label="Tambah Unit"
        icon="pi pi-plus"
        severity="success"
        size="small"
        @click="$emit('create')"
      />

      <div class="flex items-center gap-2">
        <label for="unit-status-filter" class="text-sm font-medium">
          Filter Status:
        </label>
        <Select
          id="unit-status-filter"
          v-model="statusFilter"
          :options="[
            { label: 'Semua Status', value: '' },
            { label: 'Tersedia', value: 'available' },
            { label: 'Dipinjam', value: 'lent' },
            { label: 'Nonaktif', value: 'nonactive' },
          ]"
          option-label="label"
          option-value="value"
          class="w-40"
          :show-clear="!!statusFilter"
          @clear="statusFilter = ''"
        />
      </div>
    </div>

    <DataTable
      lazy
      v-model:selection="selectedRows"
      :value="units"
      :loading="loading"
      paginator
      :first="(currentPage - 1) * perPage"
      :rows="perPage"
      :totalRecords="total"
      :current-page-report-template="'Menampilkan {first} sampai {last} dari {totalRecords} unit'"
      current-page-report-position="left"
      responsive-layout="scroll"
      striped-rows
      class="text-sm"
      @page="onPageChange"
    >
      <Column field="code" header="Kode Unit" style="width: 12rem">
        <template #body="{ data: unit }">
          <div>
            <div class="font-semibold">{{ unit.code }}</div>
            <div v-if="unit.tool" class="text-xs text-surface-400 mt-1">
              {{ unit.tool.name }}
            </div>
          </div>
        </template>
      </Column>

      <Column field="status" header="Status" style="width: 10rem">
        <template #body="{ data: unit }">
          <Tag
            :value="statusMap[unit.status]?.label"
            :severity="statusMap[unit.status]?.severity"
            rounded
          />
        </template>
      </Column>

      <Column header="Kondisi Terkini" style="width: 13rem">
        <template #body="{ data: unit }">
          <div v-if="unit.latest_condition" class="space-y-1">
            <Tag
              :value="conditionMap[unit.latest_condition.conditions]?.label"
              :severity="
                conditionMap[unit.latest_condition.conditions]?.severity
              "
              rounded
            />
            <div class="text-xs text-surface-400 mt-1">
              {{ formatDate(unit.latest_condition.recorded_at) }}
            </div>
          </div>
          <span v-else class="text-surface-400 text-sm">Belum ada</span>
        </template>
      </Column>

      <Column field="notes" header="Catatan" style="width: 16rem">
        <template #body="{ data: unit }">
          <p v-if="unit.notes" class="text-sm text-surface-600 line-clamp-2">
            {{ unit.notes }}
          </p>
          <span v-else class="text-surface-400">—</span>
        </template>
      </Column>

      <Column header="Dibuat" style="width: 10rem">
        <template #body="{ data: unit }">
          <div class="text-sm">{{ formatDate(unit.created_at) }}</div>
        </template>
      </Column>

      <Column
        header="Aksi"
        style="width: 18rem"
        header-class="text-center"
        body-class="text-center"
      >
        <template #body="{ data: unit }">
          <div class="flex gap-1 justify-center flex-wrap">
            <Button
              icon="pi pi-eye"
              rounded
              text
              severity="info"
              size="small"
              v-tooltip.top="'Detail & Riwayat'"
              @click="handleViewDetail(unit)"
            />
            <Button
              icon="pi pi-check-circle"
              rounded
              text
              severity="help"
              size="small"
              v-tooltip.top="'Catat Kondisi'"
              @click="$emit('record-condition', unit)"
            />
            <Button
              icon="pi pi-trash"
              rounded
              text
              severity="danger"
              size="small"
              :disabled="unit.has_loans"
              v-tooltip.top="
                unit.has_loans
                  ? 'Unit memiliki history peminjaman, tidak bisa dihapus'
                  : 'Hapus Unit'
              "
              @click="$emit('delete', unit)"
            />
          </div>
        </template>
      </Column>

      <template #empty>
        <div class="text-center py-12 text-surface-400">
          <i class="pi pi-inbox text-4xl block mb-3" />
          <p class="text-sm">Belum ada unit untuk tool ini</p>
        </div>
      </template>
    </DataTable>
  </div>
</template>
