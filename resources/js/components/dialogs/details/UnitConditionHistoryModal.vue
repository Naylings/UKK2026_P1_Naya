<script setup lang="ts">
import { computed } from "vue";
import type { ToolUnit, UnitCondition } from "@/types/toolunit";

// ── Loan Type Definition ──────────────────────────────────────────────────

interface LoanRecord {
    id: number;
    loan_date: string;
    due_date: string;
    borrower_name: string;
    status: "active" | "returned" | "overdue";
}

// ── Props & Emits ─────────────────────────────────────────────────────────

interface Props {
    visible: boolean;
    unit?: ToolUnit | null;
    toolName?: string;
    conditionHistory?: UnitCondition[];
    loans?: LoanRecord[];
    loading?: boolean;
    mode?: "admin" | "loan";
}

interface Emits {
    (e: "update:visible", value: boolean): void;
    (e: "record-condition"): void;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    conditionHistory: () => [],
    loans: () => [],
    mode: "admin",
});

const emit = defineEmits<Emits>();

// ── Computed ────────────────────────────────────────────────────────────

const isAdmin = computed(() => props.mode === "admin");

const statusLabel = computed(() => {
    if (!props.unit) return "";
    const map: Record<string, string> = {
        available: "Tersedia",
        lent: "Dipinjam",
        nonactive: "Nonaktif",
    };
    return map[props.unit.status] || props.unit.status;
});

const statusSeverity = computed(() => {
    if (!props.unit) return "secondary";
    const map: Record<string, string> = {
        available: "success",
        lent: "warning",
        nonactive: "danger",
    };
    return map[props.unit.status] || "secondary";
});

const sortedHistory = computed(() => {
    return [...props.conditionHistory].sort(
        (a, b) =>
            new Date(b.recorded_at).getTime() -
            new Date(a.recorded_at).getTime(),
    );
});

const filteredLoans = computed(() => {
    if (!props.loans) return [];
    return props.loans;
});

// ── Methods ─────────────────────────────────────────────────────────────

function handleClose() {
    emit("update:visible", false);
}

function handleRecordCondition() {
    if (!isAdmin.value) return;
    emit("record-condition");
}

function getConditionLabel(c: string) {
    return (
        {
            good: "Baik",
            broken: "Rusak",
            maintenance: "Maintenance",
        }[c] || c
    );
}

function getConditionSeverity(c: string) {
    return (
        {
            good: "success",
            broken: "danger",
            maintenance: "warning",
        }[c] || "secondary"
    );
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function formatTime(d: string) {
    return new Date(d).toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
    });
}

function getLoanStatusBadge(status: string) {
    const map: Record<string, { label: string; severity: string }> = {
        active: { label: "Aktif", severity: "success" },
        returned: { label: "Kembali", severity: "secondary" },
        overdue: { label: "Terlambat", severity: "danger" },
    };
    return map[status] || { label: status, severity: "secondary" };
}
</script>
<template>
    <Dialog
        :visible="visible"
        header="Detail Unit & Riwayat"
        modal
        :closable="!loading"
        class="w-full sm:w-11/12 lg:w-4/5 xl:w-2/3"
        content-class="p-0"
        @update:visible="handleClose"
    >
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary-50 rounded-lg">
                    <i class="pi pi-box text-primary-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-surface-900 m-0">
                        Detail Unit
                    </h3>
                    <p
                        class="text-xs text-surface-500 font-medium uppercase tracking-wider"
                        v-if="unit"
                    >
                        {{ unit.code }}
                    </p>
                </div>
            </div>
        </template>

        <div v-if="unit" class="p-6 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div
                    class="lg:col-span-2 bg-white border border-surface-200 shadow-sm rounded-2xl p-5 flex flex-col justify-between"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <p
                                class="text-xs font-bold text-surface-400 uppercase mb-1"
                            >
                                Nama Alat / Tool
                            </p>
                            <h2
                                class="text-2xl font-extrabold text-surface-900"
                            >
                                {{ toolName || unit.tool?.name || "-" }}
                            </h2>
                        </div>
                        <Tag
                            :value="statusLabel"
                            :severity="statusSeverity"
                            class="px-4 py-1 text-sm font-bold shadow-sm"
                        />
                    </div>

                    <div class="mt-6 flex gap-6">
                        <div>
                            <p
                                class="text-xs font-semibold text-surface-400 mb-1"
                            >
                                Kode Unit
                            </p>
                            <p
                                class="font-mono font-bold text-lg text-primary-700 bg-primary-50 px-2 py-1 rounded"
                            >
                                {{ unit.code }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-semibold text-surface-400 mb-1"
                            >
                                Terdaftar Sejak
                            </p>
                            <p class="text-sm font-medium text-surface-700">
                                {{ formatDate(unit.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-surface-900 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden"
                >
                    <div class="relative z-10">
                        <p
                            class="text-xs font-bold text-surface-400 uppercase mb-3"
                        >
                            Kondisi Terakhir
                        </p>
                        <div v-if="unit.latest_condition">
                            <Tag
                                :value="
                                    getConditionLabel(
                                        unit.latest_condition.conditions,
                                    )
                                "
                                :severity="
                                    getConditionSeverity(
                                        unit.latest_condition.conditions,
                                    )
                                "
                                class="mb-3"
                            />
                            <p class="text-sm opacity-90 line-clamp-2 italic">
                                "{{
                                    unit.latest_condition.notes ||
                                    "Tidak ada catatan"
                                }}"
                            </p>
                            <div class="mt-4 pt-4 border-t border-surface-700">
                                <p
                                    class="text-[10px] text-surface-400 uppercase font-bold"
                                >
                                    Terakhir diperiksa
                                </p>
                                <p class="text-xs font-medium">
                                    {{
                                        formatDate(
                                            unit.latest_condition.recorded_at,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="py-4 text-center">
                            <p class="text-xs text-surface-400">
                                Belum ada data kondisi
                            </p>
                        </div>
                    </div>
                    <i
                        class="pi pi-shield absolute -bottom-4 -right-4 text-8xl opacity-10"
                    ></i>
                </div>
            </div>

            <div v-if="isAdmin" class="xl:col-span-3 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-surface-800">
                        Aktivitas Peminjaman
                    </h3>
                    <span
                        class="text-xs bg-surface-100 px-3 py-1 rounded-full font-bold"
                    >
                        Total: {{ filteredLoans.length }}
                    </span>
                </div>

                <div v-if="filteredLoans.length > 0" class="space-y-3">
                    <div
                        v-for="loan in filteredLoans"
                        :key="loan.id"
                        class="p-4 bg-white border rounded-xl flex justify-between"
                    >
                        <div>
                            <p class="font-bold text-sm">
                                {{ loan.borrower_name }}
                            </p>
                            <p class="text-xs text-surface-500">
                                {{ formatDate(loan.loan_date) }} -
                                {{ formatDate(loan.due_date) }}
                            </p>
                        </div>

                        <Tag
                            :value="getLoanStatusBadge(loan.status).label"
                            :severity="getLoanStatusBadge(loan.status).severity"
                        />
                    </div>
                </div>

                <div v-else class="text-center text-sm text-surface-400 py-6">
                    Tidak ada riwayat peminjaman
                </div>
            </div>

            <div class="pt-4">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-surface-800">
                            Riwayat Kondisi Unit
                        </h3>
                        <p class="text-sm text-surface-500">
                            Log perubahan status fisik unit secara berkala
                        </p>
                    </div>
                    <Button
                        v-if="isAdmin"
                        label="Input Kondisi"
                        icon="pi pi-plus"
                        severity="success"
                        rounded
                        @click="handleRecordCondition"
                    />
                </div>

                <DataTable
                    :value="sortedHistory"
                    class="modern-table p-datatable-sm"
                    :rows="5"
                    paginator
                    responsive-layout="stack"
                    breakpoint="960px"
                >
                    <Column header="Waktu Pemeriksaan">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-bold text-surface-800">{{
                                    formatDate(data.recorded_at)
                                }}</span>
                                <span class="text-xs text-surface-400">{{
                                    formatTime(data.recorded_at)
                                }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column header="Kondisi">
                        <template #body="{ data }">
                            <Tag
                                :value="getConditionLabel(data.conditions)"
                                :severity="
                                    getConditionSeverity(data.conditions)
                                "
                                rounded
                            />
                        </template>
                    </Column>
                    <Column header="Catatan / Keterangan" style="width: 40%">
                        <template #body="{ data }">
                            <div class="text-sm">
                                <p class="text-surface-600 leading-relaxed">
                                    {{ data.notes || "-" }}
                                </p>
                                <div
                                    v-if="data.return_id"
                                    class="mt-1 flex items-center gap-1 text-[10px] font-bold text-primary-500 bg-primary-50 w-fit px-2 py-0.5 rounded"
                                >
                                    <i class="pi pi-sync text-[10px]"></i>
                                    LOG PENGEMBALIAN #{{ data.return_id }}
                                </div>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <template #footer>
            <div class="p-2 border-t border-surface-100 flex justify-end">
                <Button
                    label="Tutup Detail"
                    icon="pi pi-times"
                    text
                    severity="secondary"
                    @click="handleClose"
                    class="font-bold"
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
/* Modern Table */
:deep(.modern-table .p-datatable-thead > tr > th) {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 1rem;
}

:deep(.modern-table .p-datatable-tbody > tr) {
    transition: background 0.2s;
}

:deep(.modern-table .p-datatable-tbody > tr:hover) {
    background: #f8fafc !important;
}

:deep(.p-tag) {
    font-size: 0.7rem;
    padding: 0.25rem 0.75rem;
}
</style>
