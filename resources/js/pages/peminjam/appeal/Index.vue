<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useUserAppeal } from "./composables/useUserAppeal";
import { useFormatter } from "@/utils/useFormatter";

const { formatDate } = useFormatter();

const {
  store,
  showDialog,
  reason,
  openDialog,
  closeDialog,
  submit,
} = useUserAppeal();

const globalFilter = ref("");

let timeout: any;
watch(globalFilter, (val) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    store.fetchMy({ search: val, page: 1 });
  }, 300);
});

const isValid = computed(() => reason.value.trim().length > 0);

function getStatusLabel(status: string) {
  if (status === "approved") return "Disetujui";
  if (status === "rejected") return "Ditolak";
  return "Menunggu";
}

function getStatusSeverity(status: string) {
  if (status === "approved") return "success";
  if (status === "rejected") return "danger";
  return "warn";
}
</script>

<template>
  <div class="card">
    <div class="flex justify-between items-center mb-4">
      <div class="font-semibold text-xl">Riwayat Banding</div>
      <Button label="Ajukan Banding" icon="pi pi-plus" @click="openDialog" />
    </div>

    <div class="space-y-2 mb-3">
      <Message v-if="store.error" severity="error">
        {{ store.error }}
      </Message>

      <Message v-if="store.successMessage" severity="success">
        {{ store.successMessage }}
      </Message>
    </div>

    <div class="flex gap-3 mb-4">
      <InputText v-model="globalFilter" placeholder="Cari alasan..." />
    </div>

    <DataTable :value="store.myAppeals" :loading="store.loading" paginator :rows="10">
      <Column header="No">
        <template #body="{ index }">
          {{ store.meta?.current_page && store.meta.per_page
            ? (store.meta.current_page - 1) * store.meta.per_page + index + 1
            : index + 1 }}
        </template>
      </Column>

      <Column field="reason" header="Alasan" />

      <Column header="Status">
        <template #body="{ data }">
          <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
        </template>
      </Column>

      <Column header="Perubahan Credit">
        <template #body="{ data }">
          <span :class="{
            'text-green-600': (data.credit_changed ?? 0) > 0,
            'text-red-600': (data.credit_changed ?? 0) < 0,
          }">
            {{ data.credit_changed }}
          </span>
        </template>
      </Column>

      <Column field="admin_notes" header="Catatan Admin" />

      <Column header="Tanggal">
        <template #body="{ data }">
          <div class="text-sm">
            <div class="text-blue-600 font-medium">
              {{ formatDate(data.created_at) }}
            </div>
            <div class="text-gray-400 text-xs">
              Review: {{ data.reviewed_at ? formatDate(data.reviewed_at) : "-" }}
            </div>
          </div>
        </template>
      </Column>

      <template #empty>
        <div class="text-center text-gray-500 py-6">
          Belum ada data banding
        </div>
      </template>
    </DataTable>

    <CreateAppealForm v-model:visible="showDialog" v-model:reason="reason" :loading="store.creating" :isValid="isValid"
      @submit="submit" />
  </div>
</template>