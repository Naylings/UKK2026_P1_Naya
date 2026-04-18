<script setup lang="ts">
import { ref, watch } from "vue";
import { useFormatter } from "@/utils/useFormatter";
import type { ReturnResponse } from "@/types/return";

const { formatDate } = useFormatter();

const props = defineProps<{
  returns: ReturnResponse[];
  loading: boolean;
  meta: any;
  filters: {
    reviewed: string;
    search: string;
    page: number;
    per_page: number;
  };
  statusOptions: Array<{ label: string; value: string }>;
}>();

const emit = defineEmits<{
  (e: "update:filters"): void;
  (e: "page-change", value: any): void;
  (e: "review", item: ReturnResponse): void;
  (e: "detail", item: ReturnResponse): void;
  (e: "reset"): void;
}>();

const localSearch = ref(props.filters.search);

Debounce search
let timeout: any;
watch(localSearch, (val) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    props.filters.search = val;
    props.filters.page = 1;
    emit("update:filters");
  }, 500);
});

function getReturnStatusLabel(item: any) {
  if (!item.employee) return "Menunggu Verifikasi";
  return "Terverifikasi";
}

function getReturnStatusSeverity(item: any) {
  if (!item.employee) return "warn";
  return "success";
}
</script>

<template>
  <div class="card">
    <div class="font-semibold text-xl mb-4">Pengembalian Barang</div>

    <!-- FILTER BAR -->
    <div
      class="flex flex-wrap gap-3 mb-6 bg-gray-50 p-4 rounded-2xl border border-gray-100"
    >
      <div class="flex-1 min-w-62.5">
        <InputGroup>
          <InputGroupAddon>
            <i class="pi pi-search"></i>
          </InputGroupAddon>
          <InputText
            v-model="localSearch"
            placeholder="Cari peminjam atau alat..."
          />
        </InputGroup>
      </div>

      <Select
        v-model="props.filters.reviewed"
        :options="statusOptions"
        optionLabel="label"
        optionValue="value"
        placeholder="Status Verifikasi"
        class="w-full md:w-56"
        @change="emit('update:filters')"
      />

      <Button
        icon="pi pi-filter-slash"
        label="Reset"
        severity="secondary"
        text
        @click="emit('reset')"
      />
      <Button
        icon="pi pi-refresh"
        severity="info"
        @click="emit('update:filters')"
        :loading="loading"
      />
    </div>

    <!-- TABLE -->
    <DataTable
      :value="returns"
      :loading="loading"
      paginator
      :rows="filters.per_page"
      :totalRecords="meta?.total || 0"
      :first="(filters.page - 1) * filters.per_page"
      lazy
      @page="(e) => emit('page-change', e)"
      stripedRows
      responsiveLayout="scroll"
      class="text-sm"
    >
      <Column header="Peminjam">
        <template #body="{ data }">
          <div class="flex items-center gap-3">
          
            <div>
              <p class="font-bold text-gray-800">
                {{ data.loan?.user?.details?.name || "User" }}
              </p>
              <p class="text-[10px] text-gray-400 font-mono">
                {{ data.loan?.user?.email }}
              </p>
            </div>
          </div>
        </template>
      </Column>

      <Column header="Alat & Unit">
        <template #body="{ data }">
          <div class="flex flex-col gap-1">
            <span class="font-semibold text-gray-700">{{
              data.loan?.tool?.name
            }}</span>
            <Tag
              :value="data.loan?.unit?.code"
              severity="secondary"
              class="w-fit font-mono"
            />
          </div>
        </template>
      </Column>

      <Column header="Waktu Kembali">
        <template #body="{ data }">
          <div class="flex flex-col">
            <span class="font-medium">{{ formatDate(data.return_date) }}</span>
            <span class="text-[10px] text-gray-400"
              >Diajukan: {{ formatDate(data.created_at) }}</span
            >
          </div>
        </template>
      </Column>

      <Column header="Status">
        <template #body="{ data }">
          <div class="flex flex-col gap-1 items-start">
            <Tag
              :value="getReturnStatusLabel(data)"
              :severity="getReturnStatusSeverity(data)"
            />
            <Tag
              v-if="data.violation"
              value="Ada Pelanggaran"
              severity="danger"
            />
          </div>
        </template>
      </Column>

      <Column header="Aksi" alignFrozen="right" frozen>
        <template #body="{ data }">
          <div class="flex gap-2">
            <Button
              v-if="!data.employee"
              icon="pi pi-check-square"
              label="Verifikasi"
              size="small"
              severity="info"
              @click="emit('review', data)"
            />
            <Button
              icon="pi pi-eye"
              text
              rounded
              v-tooltip.top="'Lihat Detail'"
              @click="emit('detail', data)"
            />
          </div>
        </template>
      </Column>

      <template #empty>
        <div
          class="flex flex-col items-center justify-center py-12 text-gray-400"
        >
          <i class="pi pi-inbox text-5xl mb-4"></i>
          <p>Tidak ada pengembalian yang ditemukan</p>
        </div>
      </template>
    </DataTable>
  </div>
</template>
