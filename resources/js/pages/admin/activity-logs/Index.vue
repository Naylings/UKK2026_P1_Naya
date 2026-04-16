<script setup lang="ts">
import { computed, onBeforeMount, watch } from "vue";
import { useActivityLogs } from "./composable/useActivityLogs";

const {
  filters,
  loading,
  logs,
  meta,
  loadLogs,
  onPageChange,
  clearFilter,
  showDetailModal,
  selectedLog,
  openDetailModal,
  closeDetailModal,
} = useActivityLogs();

const moduleOptions = [
  { label: "Semua Modul", value: "" },
  { label: "Users", value: "users" },
  { label: "Categories", value: "categories" },
  { label: "Tools", value: "tools" },
  { label: "Tool Units", value: "tool_units" },
  { label: "Loans", value: "loans" },
  { label: "Returns", value: "returns" },
  { label: "Violations", value: "violations" },
  { label: "Settlements", value: "settlements" },
  { label: "App Config", value: "app_config" },
];

const allActionOptions = [
  { label: "Semua Aksi", value: "", module: "" },
  { label: "user.created", value: "user.created", module: "users" },
  { label: "user.updated", value: "user.updated", module: "users" },
  { label: "user.deleted", value: "user.deleted", module: "users" },
  { label: "user.credit_updated", value: "user.credit_updated", module: "users" },
  { label: "category.created", value: "category.created", module: "categories" },
  { label: "category.updated", value: "category.updated", module: "categories" },
  { label: "category.deleted", value: "category.deleted", module: "categories" },
  { label: "tool.created", value: "tool.created", module: "tools" },
  { label: "tool.updated", value: "tool.updated", module: "tools" },
  { label: "tool.deleted", value: "tool.deleted", module: "tools" },
  { label: "tool_unit.created", value: "tool_unit.created", module: "tool_units" },
  { label: "tool_unit.bulk_created", value: "tool_unit.bulk_created", module: "tool_units" },
  { label: "tool_unit.updated", value: "tool_unit.updated", module: "tool_units" },
  { label: "tool_unit.deleted", value: "tool_unit.deleted", module: "tool_units" },
  { label: "tool_unit.condition_recorded", value: "tool_unit.condition_recorded", module: "tool_units" },
  { label: "loan.created", value: "loan.created", module: "loans" },
  { label: "loan.approved", value: "loan.approved", module: "loans" },
  { label: "loan.rejected", value: "loan.rejected", module: "loans" },
  { label: "return.created", value: "return.created", module: "returns" },
  { label: "return.confirmed", value: "return.confirmed", module: "returns" },
  { label: "violation.created", value: "violation.created", module: "violations" },
  { label: "settlement.created", value: "settlement.created", module: "settlements" },
  { label: "app_config.updated", value: "app_config.updated", module: "app_config" },
];

// 2. Buat computed property untuk memfilter opsi aksi
const actionOptions = computed(() => {
  // Jika tidak ada modul yang dipilih (Semua Modul), tampilkan semua aksi
  if (!filters.module) {
    return allActionOptions;
  }

  // Filter aksi yang memiliki module yang sama dengan filter yang terpilih
  // Kita tetap sertakan "Semua Aksi" (value: "") agar user bisa reset filter aksi
  return allActionOptions.filter(
    (option) => option.value === "" || option.module === filters.module
  );
});

// 3. (Opsional) Reset filter action jika modul diganti agar tidak terjadi mismatch
watch(() => filters.module, () => {
  filters.action = ""; 
});

const prettyMeta = computed(() =>
  selectedLog.value?.meta
    ? JSON.stringify(selectedLog.value.meta, null, 2)
    : "-",
);

onBeforeMount(() => {
  loadLogs();
});
</script>

<template>
  <div class="space-y-6">
    <ActivityLogsTable
      :logs="logs"
      :loading="loading"
      :meta="meta"
      :filters="filters"
      :module-options="moduleOptions"
      :action-options="actionOptions"
      @update:filters="loadLogs"
      @page-change="onPageChange"
      @detail="openDetailModal"
      @reset="clearFilter"
    />

    <Dialog
      v-model:visible="showDetailModal"
      modal
      header="Detail Activity Log"
      style="width: 760px"
      :closable="true"
    >
      <div v-if="selectedLog" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="rounded-xl border p-4">
            <p class="text-xs text-gray-400">User</p>
            <p class="font-medium">
              {{ selectedLog.user?.details?.name || selectedLog.user?.email || "Sistem" }}
            </p>
          </div>

          <div class="rounded-xl border p-4">
            <p class="text-xs text-gray-400">IP Address</p>
            <p class="font-medium">{{ selectedLog.ip_address || "-" }}</p>
          </div>

          <div class="rounded-xl border p-4">
            <p class="text-xs text-gray-400">Modul</p>
            <p class="font-medium">{{ selectedLog.module }}</p>
          </div>

          <div class="rounded-xl border p-4">
            <p class="text-xs text-gray-400">Action</p>
            <p class="font-mono text-sm">{{ selectedLog.action }}</p>
          </div>
        </div>

        <div class="rounded-xl border p-4">
          <p class="text-xs text-gray-400">Deskripsi</p>
          <p class="mt-1 leading-relaxed">{{ selectedLog.description }}</p>
        </div>

        <div class="rounded-xl border p-4">
          <p class="text-xs text-gray-400">Meta</p>
          <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-950 p-4 text-xs text-slate-100">{{ prettyMeta }}</pre>
        </div>

        <div class="rounded-xl border p-4">
          <p class="text-xs text-gray-400">Waktu</p>
          <p class="font-medium">
            {{ selectedLog.created_at ? new Date(selectedLog.created_at).toLocaleString() : "-" }}
          </p>
        </div>
      </div>

      <template #footer>
        <Button label="Tutup" severity="secondary" outlined @click="closeDetailModal" />
      </template>
    </Dialog>
  </div>
</template>
