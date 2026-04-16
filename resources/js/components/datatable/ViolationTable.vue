<script setup lang="ts">
import { ref, watch } from "vue";

const props = defineProps<{
  violations: any[];
  loading: boolean;
  meta: any;
  filters: any;
  statusOptions: Array<{ label: string; value: string }>;
  typeOptions: Array<{ label: string; value: string }>;
}>();

const emit = defineEmits<{
  (e: "update:filters", value: any): void;
  (e: "page-change", value: any): void;
  (e: "search", value: string): void;
  (e: "reset"): void;
  (e: "detail", violation: any): void;
}>();

const globalFilter = ref("");

// Debounce search agar tidak terlalu sering hit API
let timeout: any;
watch(globalFilter, (val) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    emit("search", val);
  }, 300);
});

const getStatusSeverity = (status: string) => {
  switch (status?.toLowerCase()) {
    case "settled":
      return "success";
    case "active":
      return "warn";
    default:
      return "secondary";
  }
};
</script>

<template>
  <div class="card">
    <div class="font-semibold text-xl mb-4">Daftar Pelanggaran (Violation)</div>

    <div
      class="mb-6 flex flex-wrap gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4"
    >
      <div class="min-w-62.5 flex-1">
        <InputGroup>
          <InputGroupAddon>
            <i class="pi pi-search"></i>
          </InputGroupAddon>
          <InputText
            v-model="globalFilter"
            placeholder="Cari user atau alat..."
          />
        </InputGroup>
      </div>

      <Select
        :modelValue="filters.status"
        :options="statusOptions"
        optionLabel="label"
        optionValue="value"
        placeholder="Status"
        class="w-full md:w-56"
        @update:modelValue="
          (val) => emit('update:filters', { ...filters, status: val, page: 1 })
        "
      />

      <Select
        :modelValue="filters.type"
        :options="typeOptions"
        optionLabel="label"
        optionValue="value"
        placeholder="Tipe"
        class="w-full md:w-56"
        @update:modelValue="
          (val) => emit('update:filters', { ...filters, type: val, page: 1 })
        "
      />

      <Button
        icon="pi pi-filter-slash"
        label="Reset"
        severity="secondary"
        text
        @click="$emit('reset')"
      />
      <Button
        icon="pi pi-refresh"
        severity="info"
        :loading="loading"
        @click="emit('update:filters', { ...filters })"
      />
    </div>

    <DataTable
      :value="violations"
      :loading="loading"
      paginator
      lazy
      :rows="meta?.per_page || 10"
      :totalRecords="meta?.total || 0"
      class="p-datatable-sm"
      @page="(e) => $emit('page-change', e)"
    >
      <Column header="#" headerStyle="width: 3rem">
        <template #body="{ index }">
          <span class="text-secondary">
            {{ (meta?.current_page - 1) * (meta?.per_page || 10) + index + 1 }}
          </span>
        </template>
      </Column>

      <Column header="User & Alat">
        <template #body="{ data }">
          <div class="flex flex-col">
            <span class="font-bold text-surface-900 dark:text-surface-0">
              {{ data.user?.details?.name || "-" }}
            </span>
            <small class="text-muted-color">{{ data.loan?.tool?.name || "-" }}</small>
          </div>
        </template>
      </Column>

      <Column header="Tipe">
        <template #body="{ data }">
          <Tag 
            :value="data.type?.toUpperCase()" 
            severity="danger" 
            variant="outline"
            style="font-size: 10px"
          />
        </template>
      </Column>

      <Column header="Denda">
        <template #body="{ data }">
          <span class="font-semibold text-red-500">
            Rp {{ data.fine?.toLocaleString() || 0 }}
          </span>
        </template>
      </Column>

      <Column header="Status">
        <template #body="{ data }">
          <Tag
            :value="data.status?.toUpperCase()"
            :severity="getStatusSeverity(data.status)"
          />
        </template>
      </Column>

      <Column
        header="Aksi"
        headerStyle="width: 5rem"
        bodyStyle="text-align: center"
      >
        <template #body="{ data }">
          <div class="flex justify-center">
            <Button
              icon="pi pi-search"
              v-tooltip.top="'Lihat Detail'"
              severity="secondary"
              rounded
              text
              @click="$emit('detail', data)"
            />
          </div>
        </template>
      </Column>

      <template #empty>
        <div class="text-center p-4 text-muted-color">Tidak ada data ditemukan.</div>
      </template>
    </DataTable>
  </div>
</template>
