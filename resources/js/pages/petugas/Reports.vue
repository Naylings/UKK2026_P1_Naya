<script setup lang="ts">
import { useReport } from "./useReport";

const {
  store,
  showFormDialog,
  formData,
  reportTypes,
  selectedTypeLabel,
  hasDateFilters,
  hasPreviewData,
  isFormValid,
  columns,
  openFormDialog,
  closeFormDialog,
  submitForm,
  handleExport,
  backToForm,
} = useReport();

const formatValue = (value: any) => {
  if (value === null || value === undefined) return "-";
  if (typeof value === "boolean") return value ? "Ya" : "Tidak";
  if (typeof value === "number") return value.toLocaleString("id-ID");
  return String(value);
};
</script>
<template>
  <div class="card space-y-6">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
      <div>
        <h2 class="font-semibold text-xl">Laporan Sistem (Reporting)</h2>
        <p class="text-muted-color text-sm">
          Pilih jenis laporan dan periode untuk mengekspor data ke Excel.
        </p>
      </div>

      <div v-if="hasPreviewData" class="flex gap-2">
        <Button label="Ganti" icon="pi pi-arrow-left" severity="secondary" text @click="backToForm" />
        <Button label="Export" icon="pi pi-file-excel" severity="success" :loading="store.exporting"
          @click="handleExport" />
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

        <div class="md:col-span-3">
          <label class="label">Jenis Laporan</label>
          <Select v-model="formData.type" :options="reportTypes" optionLabel="label" optionValue="value"
            placeholder="Pilih jenis laporan" class="w-full" />
        </div>

<template v-if="hasDateFilters">
        <div class="md:col-span-2">
          <label class="label">Tanggal Mulai</label>
          <DatePicker v-model="formData.start_date" dateFormat="yy-mm-dd" class="w-full" />
        </div>

        <div class="md:col-span-2">
          <label class="label">Tanggal Akhir</label>
          <DatePicker v-model="formData.end_date" dateFormat="yy-mm-dd" class="w-full" />
        </div>
</template>
        
        <div class="md:col-span-3">
          <label class="label">
            {{ formData.type ? 'Search' : 'Pencarian' }}
          </label>
          <InputText v-model="formData.search" placeholder="Cari data..." class="w-full" />
        </div>

        <div class="md:col-span-2 flex gap-2">
          <Button icon="pi pi-filter-slash" severity="secondary" text v-tooltip.top="'Reset'" @click="backToForm" />
          <Button label="Preview" icon="pi pi-search" class="w-full" :loading="store.loading" :disabled="!isFormValid"
            @click="submitForm" />
        </div>

      </div>
    </div>

    <div v-if="hasPreviewData" class="space-y-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Tag severity="info" :value="selectedTypeLabel" />
          <span class="text-sm text-muted-color">
            {{ store.previewData.length }} baris
          </span>
        </div>
      </div>

      <DataTable :value="store.previewData" :loading="store.loading" paginator :rows="10"
        class="p-datatable-sm rounded-xl overflow-hidden" stripedRows removableSort>
        <Column v-for="col in columns" :key="col.field" :field="col.field" :header="col.header" sortable
          style="width: auto; white-space: nowrap;"> <template #body="{ data }">
            {{ formatValue((data as any)[col.field]) }}
          </template>
        </Column>

        <template #empty>
          <div class="text-center py-10 text-muted-color">
            <i class="pi pi-inbox text-3xl mb-2"></i>
            <p>Data tidak ditemukan</p>
          </div>
        </template>
      </DataTable>
    </div>

    <div v-else
      class="flex flex-col items-center justify-center py-16 border-2 border-dashed border-gray-200 rounded-2xl text-center">
      <div class="bg-blue-50 p-4 rounded-full mb-4">
        <i class="pi pi-chart-bar text-3xl text-blue-500"></i>
      </div>
      <h3 class="text-lg font-medium">Siap Membuat Laporan?</h3>
      <p class="text-muted-color max-w-sm">
        Pilih jenis laporan dan rentang tanggal untuk melihat preview sebelum export.
      </p>
    </div>
  </div>
</template>