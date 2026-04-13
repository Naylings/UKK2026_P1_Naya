<script setup lang="ts">
import { useToolManagement } from "@/pages/admin/tools/composable/useToolManagement";
import { onBeforeMount } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const {
  toolStore,
  filters,
  loadTools,
  onPageChange,
} = useToolManagement();

onBeforeMount(() => {
  loadTools();
});
</script>

<template>
  <div class="card">
    <div class="font-semibold text-xl mb-4">Data Tools</div>

    <UserToolsTable
      :tools="toolStore.tools"
      :loading="toolStore.loading"
      :current-page="toolStore.currentPage"
      :last-page="toolStore.lastPage"
      :total="toolStore.total"
      :per-page="toolStore.perPage"
      :filters="filters"
      @page-change="onPageChange"
      @update:filters="
        (newFilters) => {
          filters = newFilters;
          loadTools({ page: 1, per_page: toolStore.perPage });
        }
      "
      @view="
        (tool) => router.push({ name: 'user tool detail', params: { id: tool.id } })
      "
      @borrow="
        (tool) => router.push({ name: 'tool borrow', params: { id: tool.id } })
      "
    />
  </div>
</template>
