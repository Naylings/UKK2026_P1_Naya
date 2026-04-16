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
  (e: "review", loan: any): void;
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

const getStatusSeverity = (status: string) => {
  switch (status.toLowerCase()) {
    case "approved":
      return "success";
    case "pending":
      return "warn";
    case "rejected":
      return "danger";
    case "returned":
      return "info";
    default:
      return "secondary";
  }
};
</script>

<template>
  <div class="card">
    <div class="font-semibold text-xl mb-4">Daftar Peminjaman</div>

    <!-- FILTER -->
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

    <!-- TABLE -->
    <DataTable
      :value="loans"
      :loading="loading"
      paginator
      :rows="meta?.per_page || 10"
      :totalRecords="meta?.total || 0"
      lazy
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

      <Column header="Informasi Peminjaman">
        <template #body="{ data }">
          <div class="flex flex-col">
            <span class="font-bold text-surface-900 dark:text-surface-0">{{
              data.tool?.name
            }}</span>
            <small class="text-muted-color">{{ data.purpose }}</small>
          </div>
        </template>
      </Column>

      <Column header="Unit">
        <template #body="{ data }">
          <Tag
            :value="data.unit?.code"
            severity="secondary"
            variant="outline"
          />
        </template>
      </Column>

      <Column header="Jadwal">
        <template #body="{ data }">
          <div class="flex items-center gap-2 text-sm">
            <i class="pi pi-calendar text-primary text-xs" />
            <div class="flex flex-col">
              <span>{{ formatDate(data.loan_date) }}</span>
              <span class="text-xs text-muted-color italic"
                >s/d {{ formatDate(data.due_date) }}</span
              >
            </div>
          </div>
        </template>
      </Column>

      <Column header="Status">
        <template #body="{ data }">
          <Tag
            :value="data.status.toUpperCase()"
            :severity="getStatusSeverity(data.status)"
          />
        </template>
      </Column>

      <Column
        header="Aksi"
        headerStyle="width: 8rem"
        bodyStyle="text-align: center"
      >
        <template #body="{ data }">
          <div class="flex gap-2 justify-center">
            <Button
              v-if="data.status === 'pending'"
              icon="pi pi-check-circle"
              v-tooltip.top="'Review'"
              severity="success"
              rounded
              text
              @click="$emit('review', data)"
            />
            <Button
              icon="pi pi-search"
              v-tooltip.top="'Detail'"
              severity="info"
              rounded
              text
              @click="$emit('detail', data)"
            />
          </div>
        </template>
      </Column>
    </DataTable>
  </div>
</template>
