<script setup lang="ts">
import { useAdminDashboard } from "./composables/useAdminDashboard";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Badge from "primevue/badge";
import Button from "primevue/button";
import Message from "primevue/message";
import Divider from "primevue/divider";
import { computed } from "vue";

const {
    loading,
    summary,
    alerts,
    stats,
    pendingLoans,
    pendingReturns,
    pendingViolations,
    recentActivities,
    refetch
} = useAdminDashboard();

const formatNumber = (val: number) =>
    new Intl.NumberFormat("id-ID").format(val);

const formatDate = (date: string) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
};

const getTimeAgo = (date: string) => {
    if (!date) return "";
    const diff = new Date().getTime() - new Date(date).getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 60) return `${minutes}m lalu`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}j lalu`;
    return formatDate(date);
};

const summaryLabels: Record<string, string> = {
    total_users: "Total Pengguna",
    total_tools: "Total Alat",
    active_loans: "Sedang Dipinjam",
    pending_loans: "Menunggu Persetujuan",
    pending_returns: "Menunggu Verifikasi",
    active_violations: "Pelanggaran Aktif"
};

const getIcon = (key: string) => {
    const icons: Record<string, string> = {
        total_users: 'pi-users',
        total_tools: 'pi-wrench',
        active_loans: 'pi-sync',
        pending_loans: 'pi-clock',
        pending_returns: 'pi-undo',
        active_violations: 'pi-exclamation-circle'
    };
    return icons[key] || 'pi-info-circle';
};

const getSeverity = (key: string, val: number) => {
    if (val === 0) return "success";
    if (key.includes("pending")) return "warning";
    if (key.includes("violation")) return "danger";
    return "info";
};

const hasAlert = computed(() => {
    if (!alerts.value) return false;
    return (
        alerts.value.overdue_loans > 0 ||
        alerts.value.unreviewed_returns > 0 ||
        alerts.value.unsettled_violations > 0 ||
        alerts.value.due_today > 0
    );
});
</script>

<template>
    <div class="p-6 max-w-7xl mx-auto space-y-5 bg-gray-50 min-h-screen">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Dashboard Admin</h1>
                <p class="text-gray-500">Pantau performa sistem dan kelola antrean operasional.</p>
            </div>
            <Button icon="pi pi-refresh" label="Muat Ulang Data" @click="refetch" :loading="loading"
                class="p-button-raised p-button-primary" />
        </div>

        <div v-if="hasAlert" class="space-y-3">
            <Message v-if="alerts?.overdue_loans > 0" severity="error" :closable="false">
                <i class="pi pi-exclamation-triangle mr-2"></i>
                <b>Urgent:</b> Ada {{ alerts.overdue_loans }} peminjaman yang melewati batas waktu pengembalian.
            </Message>
            <Message v-if="alerts?.unreviewed_returns > 0" severity="warn" :closable="false">
                <i class="pi pi-clock mr-2"></i>
                <b>Menunggu Review:</b> {{ alerts.unreviewed_returns }} alat telah kembali dan butuh verifikasi.
            </Message>
            <Message v-if="alerts?.due_today > 0" severity="info" :closable="false">
                <i class="pi pi-calendar mr-2"></i>
                <b>Perhatian:</b> {{ alerts.due_today }} peminjaman jatuh tempo hari ini.
            </Message>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <Card v-for="(val, key) in summary" :key="key" class="border-none shadow-sm overflow-hidden">
                <template #content>
                    <div class="flex items-center justify-between">

                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">
                                {{ summaryLabels[key] || key }}
                            </p>

                            <div class="flex items-end gap-2">
                                <span class="text-xl font-semibold text-gray-900 leading-none">
                                    {{ formatNumber(val) }}
                                </span>

                                <Tag v-if="getSeverity(String(key), val) !== 'success'"
                                    :severity="getSeverity(String(key), val)" value="!" rounded class="text-[10px]" />
                            </div>
                        </div>

                        <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100">
                            <i :class="['pi text-lg text-gray-500', getIcon(String(key))]"></i>
                        </div>

                    </div>
                </template>
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <div class="grid grid-cols-2 gap-4">
                    <Card class="border-l-4 border-l-blue-500 shadow-sm">
                        <template #content>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-500">Return Hari Ini</p>
                                    <p class="text-2xl font-bold">{{ stats?.returns_today ?? 0 }}</p>
                                </div>
                                <i class="pi pi-check-square text-blue-200 text-3xl"></i>
                            </div>
                        </template>
                    </Card>
                    <Card class="border-l-4 border-l-green-500 shadow-sm">
                        <template #content>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-500">Settlement Hari Ini</p>
                                    <p class="text-2xl font-bold">{{ stats?.settlements_today ?? 0 }}</p>
                                </div>
                                <i class="pi pi-dollar text-green-200 text-3xl"></i>
                            </div>
                        </template>
                    </Card>
                </div>

                <Card class="shadow-sm border-t-4 border-t-orange-400">
                    <template #title>
                        <div class="flex justify-between items-center">
                            <span>Menunggu Persetujuan</span>
                            <Badge :value="pendingLoans.length" severity="warning" />
                        </div>
                    </template>
                    <template #content>
                        <DataTable :value="pendingLoans" :rows="3" class="p-datatable-sm" responsiveLayout="scroll">
                            <Column field="user.name" header="Peminjam" />
                            <Column field="tool.name" header="Alat" />
                            <Column header="Aksi">
                                <template #body>
                                    <Button icon="pi pi-chevron-right" class="p-button-text p-button-sm" />
                                </template>
                            </Column>
                            <template #empty>
                                <div class="text-center py-4 text-gray-400 italic">Tidak ada antrean peminjaman.</div>
                            </template>
                        </DataTable>
                    </template>
                </Card>

                <Card class="shadow-sm border-t-4 border-t-primary">
                    <template #title>
                        <div class="flex justify-between items-center">
                            <span>Menunggu Pemeriksaan Alat</span>
                            <Badge :value="pendingReturns.length" severity="info" />
                        </div>
                    </template>
                    <template #content>
                        <DataTable :value="pendingReturns" :rows="5" class="p-datatable-sm">
                            <Column field="user.name" header="User" />
                            <Column field="loan.tool.name" header="Alat" />
                            <Column header="Tgl Kembali">
                                <template #body="{ data }">
                                    {{ formatDate(data.return_date) }}
                                </template>
                            </Column>
                            <template #empty>
                                <div class="text-center py-4 text-gray-400 italic">Tidak ada alat yang perlu dicek.
                                </div>
                            </template>
                        </DataTable>
                    </template>
                </Card>
            </div>

            <div class="space-y-6">

                <Card v-if="pendingViolations.length > 0" class="bg-red-50 border-none shadow-sm">
                    <template #title>
                        <span class="text-red-700 text-lg flex items-center gap-2">
                            <i class="pi pi-exclamation-triangle"></i> Pelanggaran Baru
                        </span>
                    </template>
                    <template #content>
                        <div v-for="v in pendingViolations" :key="v.id"
                            class="mb-3 p-3 bg-white rounded-lg border border-red-100 shadow-sm">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-bold text-xs uppercase text-gray-500">{{ v.user.name }}</span>
                                <Badge :value="v.type" severity="danger" />
                            </div>
                            <p class="text-sm font-semibold text-gray-800">{{ v.description }}</p>
                            <Divider class="my-2" />
                            <Button label="Proses Denda" class="p-button-danger p-button-text p-button-sm w-full" />
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm">
                    <template #title>Log Aktivitas Sistem</template>
                    <template #content>
                        <div class="space-y-4 max-h-105 overflow-y-auto pr-2">
                            <div v-for="act in recentActivities" :key="act.id" class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i :class="['pi text-xs text-blue-600',
                                            act.module === 'loan' ? 'pi-sign-out' : 'pi-sign-in']"></i>
                                    </div>
                                    <div class="w-px h-full bg-gray-100 my-1"></div>
                                </div>
                                <div class="flex-1 pb-4 border-b border-gray-100 last:border-none">
                                    <p class="text-xs font-bold text-primary uppercase">{{ act.module }}</p>
                                    <p class="text-sm text-gray-800 leading-snug mt-0.5">{{ act.description }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">
                                        <i class="pi pi-clock text-[10px]"></i> {{ getTimeAgo(act.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="recentActivities.length === 0"
                                class="text-center py-4 text-gray-400 text-sm italic">
                                Belum ada aktivitas tercatat.
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
    font-size: 1.1rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 1rem;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: transparent;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #9ca3af;
    border: none;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    border-color: #f3f4f6;
    font-size: 0.875rem;
}
</style>