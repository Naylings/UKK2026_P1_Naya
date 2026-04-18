<script setup lang="ts">
import { useUserDashboard } from "./composables/useUserDashboard";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Badge from "primevue/badge";
import Button from "primevue/button";
import Chip from "primevue/chip";
import Message from "primevue/message";
import Divider from "primevue/divider";
import { computed } from "vue";
import router from "@/router";


const {
  loading,
  summary,
  alerts,
  activeLoans,
  violations,
  returnHistory,
  recommendations,
  recentActivities,
  isOverdue,
  isNearDue,
  refetch
} = useUserDashboard();

const formatDate = (date: string) => {
  if (!date) return "-";
  return new Date(date).toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const formatCurrency = (val: number) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(val);

const getTimeAgo = (date: string) => {
  if (!date) return "";
  const diff = new Date().getTime() - new Date(date).getTime();
  const minutes = Math.floor(diff / 60000);
  if (minutes < 60) return `${minutes}m lalu`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}j lalu`;
  return formatDate(date);
};

const currentLoan = computed(() => activeLoans.value[0] || null);

const summaryLabels: Record<string, string> = {
  credit_score: 'Skor Kredit',
  is_restricted: 'Status Akses',
  has_active_loan: 'Peminjaman Aktif',
  total_violations_count: 'Total Pelanggaran'
};

const getIcon = (key: string) => {
  const icons: Record<string, string> = {
    credit_score: 'pi-star-fill',
    is_restricted: 'pi-shield',
    has_active_loan: 'pi-box',
    total_violations_count: 'pi-exclamation-circle'
  };
  return icons[key] || 'pi-info-circle';
};

const hasAlert = computed(() => {
  return (
    alerts.value.has_overdue ||
    alerts.value.is_due_soon ||
    alerts.value.has_active_violation ||
    summary.value?.is_restricted
  );
});


function goToTools() {
  router.push({ name: 'tools' })
}

</script>

<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6 bg-gray-50 min-h-screen">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Dashboard User</h1>
        <p class="text-gray-500">Pantau peminjaman dan skor kredit Anda.</p>
      </div>
      <Button icon="pi pi-refresh" label="Muat Ulang" @click="refetch" :loading="loading"
        class="p-button-raised p-button-primary" />
    </div>

    <div v-if="hasAlert" class="space-y-3">
      <Message v-if="summary?.is_restricted" severity="error" :closable="false">
        <i class="pi pi-lock mr-2"></i>
        <b>Akun Dibatasi:</b> Anda tidak dapat melakukan peminjaman baru saat ini.
      </Message>
      <Message v-if="alerts.has_overdue" severity="warn" :closable="false">
        <b>Peringatan:</b> Anda memiliki pinjaman yang melewati batas waktu!
      </Message>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card v-for="(val, key) in summary" :key="key" class="border-none shadow-sm overflow-hidden">
        <template #content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                {{ summaryLabels[key] || key }}
              </p>

              <div class="mt-1">
                <template v-if="key === 'is_restricted'">
                  <Tag :severity="val ? 'danger' : 'success'" :value="val ? 'Dibatasi' : 'Aktif'" rounded />
                </template>

                <template v-else-if="key === 'has_active_loan'">
                  <Tag :severity="val ? 'info' : 'secondary'" :value="val ? 'Ada Pinjaman' : 'Tidak Ada'" rounded />
                </template>

                <p v-else class="text-2xl font-black text-gray-800">
                  {{ val }}
                </p>
              </div>
            </div>

            <i :class="['pi text-2xl opacity-20', getIcon(key as string)]"></i>
          </div>
        </template>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2 space-y-6">

        <Card class="border-t-4 border-t-primary shadow-md">
          <template #title>
            <div class="flex justify-between items-center">
              <span>Pinjaman Saat Ini</span>
              <Badge v-if="currentLoan" value="Sedang Dipinjam" severity="info" />
            </div>
          </template>
          <template #content>
            <div v-if="currentLoan" class="py-4">
              <div class="flex items-center gap-6">
                <div class="hidden sm:flex w-20 h-20 bg-blue-50 rounded-xl items-center justify-center">
                  <i class="pi pi-desktop text-3xl text-primary"></i>
                </div>
                <div class="flex-1 space-y-2">
                  <h3 class="text-xl font-bold text-gray-800">{{ currentLoan.tool.name }}</h3>
                  <div class="flex flex-wrap gap-4 text-sm">
                    <span class="text-gray-500"><i class="pi pi-tag mr-1 text-xs"></i> Kode: <b>{{ currentLoan.unit.code
                        }}</b></span>
                    <span class="text-gray-500"><i class="pi pi-calendar mr-1 text-xs"></i> Pinjam: {{
                      formatDate(currentLoan.loan_date) }}</span>
                  </div>
                  <Divider />
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="text-xs text-gray-400 uppercase">Batas Pengembalian</p>
                      <p :class="['font-bold', isOverdue(currentLoan.due_date) ? 'text-red-600' : 'text-gray-800']">
                        {{ formatDate(currentLoan.due_date) }}
                      </p>
                    </div>
                    <Tag :value="isOverdue(currentLoan.due_date) ? 'Terlambat' : 'Aman'"
                      :severity="isOverdue(currentLoan.due_date) ? 'danger' : 'success'" />
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-10">
              <i class="pi pi-inbox text-4xl text-gray-200 mb-3"></i>
              <p class="text-gray-500 italic">Anda tidak memiliki pinjaman aktif.</p>
              <Button label="Mulai Pinjam Alat" icon="pi pi-plus" class="mt-4 p-button-text p-button-sm"
                :disabled="summary?.is_restricted" @click="goToTools" />
            </div>
          </template>
        </Card>

        <Card class="shadow-sm">
          <template #title>Riwayat Pengembalian Terakhir</template>
          <template #content>
            <DataTable :value="returnHistory" :rows="3" class="p-datatable-sm">
              <Column field="loan.tool.name" header="Alat" />
              <Column header="Tanggal Kembali">
                <template #body="{ data }">
                  <span class="text-sm text-gray-600">{{ formatDate(data.return_date) }}</span>
                </template>
              </Column>
              <Column header="Status">
                <template #body="{ data }">
                  <i v-if="data.reviewed" class="pi pi-check-circle text-green-500" v-tooltip="'Sudah Direview'"></i>
                  <i v-else class="pi pi-clock text-yellow-500" v-tooltip="'Menunggu Review'"></i>
                </template>
              </Column>
            </DataTable>
          </template>
        </Card>
      </div>

      <div class="space-y-6">

        <Card v-if="violations.length > 0" class="bg-red-50 border-none shadow-sm">
          <template #title><span class="text-red-700 text-lg flex items-center gap-2"><i
                class="pi pi-exclamation-triangle"></i> Pelanggaran</span></template>
          <template #content>
            <div v-for="v in violations" :key="v.id"
              class="mb-3 p-3 bg-white rounded-lg border border-red-100 shadow-sm">
              <div class="flex justify-between items-center mb-1">
                <span class="font-bold text-sm uppercase text-gray-700">{{ v.type }}</span>
                <Badge :value="v.status" :severity="v.status === 'settled' ? 'success' : 'danger'" />
              </div>
              <p class="text-lg font-bold text-red-600">{{ formatCurrency(v.fine) }}</p>
              <p class="text-xs text-gray-400 mt-1 italic">"{{ v.description }}"</p>
            </div>
          </template>
        </Card>

        <Card class="shadow-sm">
          <template #title>Aktivitas Terkini</template>
          <template #content>
            <div class="space-y-4">
              <div v-for="act in recentActivities" :key="act.id" class="flex gap-3">
                <div class="flex flex-col items-center">
                  <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <i
                      :class="['pi text-xs text-blue-600', act.action === 'return' ? 'pi-arrow-down-left' : 'pi-arrow-up-right']"></i>
                  </div>
                </div>
                <div class="flex-1 pb-4 border-b border-gray-100 last:border-none">
                  <p class="text-sm font-semibold text-gray-800 leading-snug">{{ act.description }}</p>
                  <p class="text-xs text-gray-400 mt-1">{{ getTimeAgo(act.created_at) }}</p>
                </div>
              </div>
              <div v-if="recentActivities.length === 0" class="text-center py-4 text-gray-400 text-sm">
                Tidak ada aktivitas.
              </div>
            </div>
          </template>
        </Card>

      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-card) {
  border-radius: 12px;
}

:deep(.p-card-title) {
  font-size: 1.15rem;
  font-weight: 800;
  color: #1f2937;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
  background: transparent;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #9ca3af;
}
</style>