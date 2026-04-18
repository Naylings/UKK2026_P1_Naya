<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useAppealStore } from "@/stores/appeal";
import { useFormatter } from "@/utils/useFormatter";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import Dialog from "primevue/dialog";
import Message from "primevue/message";
import type { Appeal } from "@/types/appeal";

const { formatDate } = useFormatter();
const store = useAppealStore();

const globalFilter = ref("");
const visibleReviewDialog = ref(false);
const selectedAppeal = ref<Appeal | null>(null);

const formData = ref({
  status: 'approved' as 'approved' | 'rejected',
  credit_changed: '',
  admin_notes: '',
});

let timeout: any;
watch(globalFilter, (val) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    store.fetchAll({ search: val });
  }, 300);
});

onMounted(() => {
  store.fetchAll();
});

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

async function openReviewDialog(appeal: Appeal) {
  selectedAppeal.value = appeal;
  formData.value = {
    status: 'approved',
    credit_changed: appeal.credit_changed?.toString() || '',
    admin_notes: appeal.admin_notes || '',
  };
  visibleReviewDialog.value = true;
}

async function submitReview() {
  if (!selectedAppeal.value) return;

  const payload: any = {
    status: formData.value.status,
    admin_notes: formData.value.admin_notes || '',
  };

  if (formData.value.status === 'approved' && formData.value.credit_changed) {
    payload.credit_changed = parseInt(formData.value.credit_changed, 10);
  }

  const success = await store.review(selectedAppeal.value.id, payload);
  if (success) {
    visibleReviewDialog.value = false;
  }
}
</script>

<template>
  <div class="card">
    <div class="mb-4 text-xl font-semibold">Pengajuan Appeal</div>


    <div class="space-y-2 mb-3">
      <Message v-if="store.error" severity="error">{{ store.error }}</Message>
      <Message v-if="store.successMessage" severity="success">{{ store.successMessage }}</Message>
    </div>

    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-6">
      <div class="flex flex-col md:flex-row md:items-center gap-3">
        <div class="p-input-icon-left flex-1">
          <InputText v-model="globalFilter" placeholder="Ketik nama user atau alasan banding..."
            class="w-full border-none shadow-none bg-transparent" />
        </div>

        <Divider layout="vertical" class="hidden md:block" />

        <div class="flex gap-2">
          <Button label="Reset" icon="pi pi-refresh" severity="secondary" text class="white-space-nowrap"
            @click="globalFilter = ''" />
        </div>
      </div>
    </div>

    <DataTable :value="store.appeals" :loading="store.loading" paginator :rows="10" responsiveLayout="scroll"
      class="p-datatable-sm">
      <Column header="No" style="width: 50px">
        <template #body="{ index }">
          {{ store.meta?.current_page && store.meta.per_page
            ? (store.meta.current_page - 1) * store.meta.per_page + index + 1
            : index + 1 }}
        </template>
      </Column>

      <Column header="User">
        <template #body="{ data }">
          <div class="font-medium text-sm">{{ data.user.details?.name || 'N/A' }}</div>
          <div class="text-xs text-gray-400">{{ data.user.details?.nik }}</div>
        </template>
      </Column>

      <Column field="reason" header="Alasan" style="min-width: 200px">
        <template #body="{ data }">
          <p class="text-sm line-clamp-2">{{ data.reason }}</p>
        </template>
      </Column>

      <Column header="Status">
        <template #body="{ data }">
          <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
        </template>
      </Column>

      <Column header="Perubahan Credit">
        <template #body="{ data }">
          <span v-if="data.status !== 'pending'" :class="{
            'text-green-600 font-semibold': (data.credit_changed ?? 0) > 0,
            'text-red-600 font-semibold': (data.credit_changed ?? 0) < 0,
            'text-gray-500': (data.credit_changed ?? 0) === 0
          }">
            {{ data.credit_changed > 0 ? '+' : '' }}{{ data.credit_changed ?? 0 }}
          </span>
          <span v-else class="text-gray-300">-</span>
        </template>
      </Column>

      <Column header="Catatan Admin">
        <template #body="{ data }">
          {{ data.admin_notes || '-' }}
        </template>
      </Column>


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

      <Column header="Aksi" style="width: 100px; text-align: center">
        <template #body="{ data }">
          <Button v-if="data.status === 'pending'" icon="pi pi-check-square" label="Review" class="p-button-sm"
            severity="primary" @click="openReviewDialog(data)" text />
          <Button v-else icon="pi pi-eye" label="Detail" class="p-button-sm" severity="secondary"
            @click="openReviewDialog(data)" text />
        </template>
      </Column>

      <template #empty>
        <div class="text-center text-gray-500 py-6">Belum ada data pengajuan banding</div>
      </template>
    </DataTable>

    <Dialog v-model:visible="visibleReviewDialog" modal
      :header="selectedAppeal?.status === 'pending' ? 'Review Appeal' : 'Detail Appeal'"
      :style="{ width: '90vw', maxWidth: '550px' }" class="p-fluid">
      <div v-if="selectedAppeal" class="space-y-3 pt-1">

        <div class="grid grid-cols-2 gap-3">
          <div class="rounded-lg border border-gray-100 p-3 bg-gray-50/50">
            <p class="text-[11px] uppercase tracking-tight text-gray-400 font-bold mb-1">Informasi Pengaju</p>
            <p class="text-sm font-semibold text-gray-800">{{ selectedAppeal.user.details?.name }}</p>
            <p class="text-xs text-gray-500">{{ selectedAppeal.user.details?.nik || 'No NIK' }}</p>
          </div>
          <div class="rounded-lg border border-gray-100 p-3 bg-gray-50/50">
            <p class="text-[11px] uppercase tracking-tight text-gray-400 font-bold mb-1">Waktu Pengajuan</p>
            <p class="text-sm font-medium text-gray-700">{{ formatDate(selectedAppeal.created_at) }}</p>
            <p class="text-[11px] text-blue-500 font-semibold uppercase">Status: {{
              getStatusLabel(selectedAppeal.status) }}
            </p>
          </div>
        </div>

        <div class="rounded-lg border border-gray-100 p-3">
          <p class="text-[11px] uppercase tracking-tight text-gray-400 font-bold mb-1">Alasan Banding (User)</p>
          <div class="text-sm leading-snug text-gray-700 bg-white italic border-l-2 border-gray-200 pl-3 py-1">
            "{{ selectedAppeal.reason }}"
          </div>
        </div>
        <div v-if="selectedAppeal.status === 'pending'"
          class="rounded-xl border border-primary/10 bg-primary/5 p-5 space-y-5">
          <div class="flex items-center gap-2">
            <i class="pi pi-pencil text-primary text-xs"></i>
            <h3 class="font-semibold text-primary text-[11px] uppercase tracking-wide">
              Tindakan Admin
            </h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2">
              <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">
                Keputusan
              </label>

              <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                  <RadioButton v-model="formData.status" inputId="s_app" name="status" value="approved"
                    class="p-radiobutton-sm" />
                  <label for="s_app" class="text-sm cursor-pointer">Setujui</label>
                </div>

                <div class="flex items-center gap-2">
                  <RadioButton v-model="formData.status" inputId="s_rej" name="status" value="rejected"
                    class="p-radiobutton-sm" />
                  <label for="s_rej" class="text-sm cursor-pointer">Tolak</label>
                </div>
              </div>
            </div>

            <div v-if="formData.status === 'approved'" class="space-y-2">
              <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">
                Penyesuaian Credit
              </label>

              <InputText v-model="formData.credit_changed" type="number" placeholder="0"
                class="p-inputtext-sm w-full" />

              <p class="text-[10px] text-gray-400 italic">
                Gunakan nilai negatif (-) untuk mengurangi credit
              </p>
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">
              Catatan Admin
            </label>

            <Textarea v-model="formData.admin_notes" rows="3" placeholder="Tulis catatan..." class="w-full text-sm" />
          </div>
        </div>

        <div v-else class="rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-3">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-[11px] uppercase text-gray-400 font-bold mb-1">Hasil Akhir</p>
              <Tag :value="getStatusLabel(selectedAppeal.status)" :severity="getStatusSeverity(selectedAppeal.status)"
                class="text-[10px]" />
            </div>
            <div v-if="selectedAppeal.status === 'approved'" class="text-right">
              <p class="text-[11px] uppercase text-gray-400 font-bold mb-1">Kredit</p>
              <span class="font-bold text-sm"
                :class="selectedAppeal.credit_changed >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ selectedAppeal.credit_changed > 0 ? '+' : '' }}{{ selectedAppeal.credit_changed }}
              </span>
            </div>
            <div class="text-right">
              <p class="text-[11px] uppercase text-gray-400 font-bold mb-1">Direview Pada</p>
              <p class="text-xs font-medium text-gray-700">{{ formatDate(selectedAppeal.reviewed_at) }}</p>
            </div>
          </div>
          <div class="pt-2 border-t border-gray-200">
            <p class="text-[11px] uppercase text-gray-400 font-bold mb-1">Tanggapan Admin</p>
            <p class="text-sm text-gray-600 italic leading-snug">"{{ selectedAppeal.admin_notes || 'Tidak ada catatan.'
            }}"
            </p>
          </div>
        </div>

      </div>

      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Tutup" icon="pi pi-times" severity="secondary" text class="p-button-sm"
            @click="visibleReviewDialog = false" />
          <Button v-if="selectedAppeal?.status === 'pending'" label="Simpan" icon="pi pi-check"
            :loading="store.reviewing" :severity="formData.status === 'approved' ? 'success' : 'danger'"
            class="p-button-sm" @click="submitReview" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
:deep(.p-datatable-sm .p-datatable-tbody > tr > td) {
  padding: 0.75rem 0.5rem;
}
</style>