<script setup lang="ts">
import { ref, watch } from "vue";
import { useFormatter } from "@/utils/useFormatter";
import type { ActivityLogItem } from "@/types/activity-log";

const { formatDate } = useFormatter();

const props = defineProps<{
  logs: ActivityLogItem[];
  loading: boolean;
  meta: any;
  filters: {
    search: string;
    module: string;
    action: string;
    page: number;
    per_page: number;
  };
  moduleOptions: Array<{ label: string; value: string }>;
  actionOptions: Array<{ label: string; value: string }>;
}>();

const emit = defineEmits<{
  (e: "update:filters"): void;
  (e: "page-change", value: any): void;
  (e: "detail", item: ActivityLogItem): void;
  (e: "reset"): void;
}>();

const localSearch = ref(props.filters.search);

let timeout: any;
watch(localSearch, (val) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    props.filters.search = val;
    props.filters.page = 1;
    emit("update:filters");
  }, 400);
});
</script>

<template>
  <div class="card">
    <div class="mb-4 text-xl font-semibold">Activity Log</div>

    <div
      class="mb-6 flex flex-wrap gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4"
    >
      <div class="min-w-62.5 flex-1">
        <InputGroup>
          <InputGroupAddon>
            <i class="pi pi-search"></i>
          </InputGroupAddon>
          <InputText
            v-model="localSearch"
            placeholder="Cari aksi, modul, deskripsi, atau user..."
          />
        </InputGroup>
      </div>

      <Select
        v-model="props.filters.module"
        :options="moduleOptions"
        optionLabel="label"
        optionValue="value"
        placeholder="Modul"
        class="w-full md:w-56"
        @change="emit('update:filters')"
      />

      <Select
        v-model="props.filters.action"
        :options="actionOptions"
        optionLabel="label"
        optionValue="value"
        placeholder="Aksi"
        class="w-full md:w-64"
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
        :loading="loading"
        @click="emit('update:filters')"
      />
    </div>

    <DataTable
      :value="logs"
      :loading="loading"
      paginator
      lazy
      :rows="filters.per_page"
      :totalRecords="meta?.total || 0"
      :first="(filters.page - 1) * filters.per_page"
      responsiveLayout="scroll"
      class="text-sm"
      @page="(e) => emit('page-change', e)"
    >
      <Column header="Waktu">
        <template #body="{ data }">
          <div class="flex flex-col">
            <span class="font-medium">{{ formatDate(data.created_at) }}</span>
            <span class="text-[10px] text-gray-400">
              {{ data.created_at ? new Date(data.created_at).toLocaleTimeString() : "-" }}
            </span>
          </div>
        </template>
      </Column>

      <Column header="User">
        <template #body="{ data }">
          <div class="flex flex-col">
            <span class="font-semibold text-gray-800">
              {{ data.user?.details?.name || data.user?.email || "Sistem" }}
            </span>
            <span class="text-[10px] text-gray-400">
              {{ data.user?.email || data.ip_address || "-" }}
            </span>
          </div>
        </template>
      </Column>

      <Column header="Modul">
        <template #body="{ data }">
          <Tag :value="data.module" severity="secondary" />
        </template>
      </Column>

      <Column header="Aksi">
        <template #body="{ data }">
          <span class="font-mono text-xs">{{ data.action }}</span>
        </template>
      </Column>

      <Column header="Deskripsi">
        <template #body="{ data }">
          <span>{{ data.description }}</span>
        </template>
      </Column>

      <Column header="Aksi UI" style="width: 100px">
        <template #body="{ data }">
          <Button
            icon="pi pi-eye"
            text
            rounded
            severity="secondary"
            @click="emit('detail', data)"
          />
        </template>
      </Column>

      <template #empty>
        <div class="py-10 text-center text-gray-500">
          Tidak ada activity log.
        </div>
      </template>
    </DataTable>
  </div>
</template>
