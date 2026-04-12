<script setup lang="ts">
import { computed, ref } from "vue";
import type { ToolUnit, UnitCondition } from "@/types/toolunit";

// ── Loan Type Definition ──────────────────────────────────────────────────

interface LoanRecord {
  id: number;
  loan_date: string; // ISO date format
  due_date: string; // ISO date format
  borrower_name: string;
  status: "active" | "returned" | "overdue";
}

// ── Props & Emits ─────────────────────────────────────────────────────────

interface Props {
  visible: boolean;
  unit?: ToolUnit | null;
  conditionHistory?: UnitCondition[];
  loans?: LoanRecord[];
  loading?: boolean;
}

interface Emits {
  (e: "update:visible", value: boolean): void;
  (e: "record-condition"): void;
  (e: "load-loans"): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  conditionHistory: () => [],
  loans: () => [],
});

const emit = defineEmits<Emits>();

// ── Reactive State ────────────────────────────────────────────────────────

const selectedDate = ref<Date | null>(null);
const currentConditionPage = ref(0);

// ── Computed ────────────────────────────────────────────────────────────

const statusLabel = computed(() => {
  if (!props.unit) return "";
  switch (props.unit.status) {
    case "available":
      return "Tersedia";
    case "lent":
      return "Dipinjam";
    case "nonactive":
      return "Nonaktif";
    default:
      return props.unit.status;
  }
});

const statusSeverity = computed(() => {
  if (!props.unit) return "secondary";
  switch (props.unit.status) {
    case "available":
      return "success";
    case "lent":
      return "warning";
    case "nonactive":
      return "danger";
    default:
      return "secondary";
  }
});

const sortedHistory = computed(() => {
  if (!props.conditionHistory) return [];
  return [...props.conditionHistory].sort((a, b) => {
    return (
      new Date(b.recorded_at).getTime() - new Date(a.recorded_at).getTime()
    );
  });
});

const totalConditionRecords = computed(() => {
  return sortedHistory.value.length;
});

// Loan-related computed properties
const activeLoansByDate = computed(() => {
  const map = new Map<string, LoanRecord[]>();

  for (const loan of props.loans) {
    const startDate = new Date(loan.loan_date);
    const endDate = new Date(loan.due_date);

    for (
      let d = new Date(startDate);
      d <= endDate;
      d.setDate(d.getDate() + 1)
    ) {
      const dateStr = formatDateKey(d);
      if (!map.has(dateStr)) {
        map.set(dateStr, []);
      }
      map.get(dateStr)!.push(loan);
    }
  }

  return map;
});

const hasLoans = computed(() => props.loans && props.loans.length > 0);

const activeLoansList = computed(() => {
  const now = new Date();
  return (props.loans || []).filter((loan) => {
    const loanEnd = new Date(loan.due_date);
    return loan.status === "active" && loanEnd >= now;
  });
});

// ── Methods ───────────────────────────────────────────────────────────────

function handleClose() {
  emit("update:visible", false);
}

function handleRecordCondition() {
  emit("record-condition");
}

function getConditionLabel(condition: string): string {
  const map: Record<string, string> = {
    good: "Baik",
    broken: "Rusak",
    maintenance: "Maintenance",
  };
  return map[condition] || condition;
}

function getConditionSeverity(condition: string): string {
  const map: Record<string, string> = {
    good: "success",
    broken: "danger",
    maintenance: "warning",
  };
  return map[condition] || "secondary";
}

function formatDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString("id-ID", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatTime(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
}

function formatDateKey(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function isLoanDate(date: Date): boolean {
  const dateStr = formatDateKey(date);
  return activeLoansByDate.value.has(dateStr);
}

function getLoanForDate(date: Date): LoanRecord | null {
  const dateStr = formatDateKey(date);
  const loans = activeLoansByDate.value.get(dateStr);
  return loans && loans.length > 0 ? loans[0] : null;
}

function getLoanStatusBadge(status: string) {
  const map: Record<string, { label: string; severity: string }> = {
    active: { label: "Aktif", severity: "success" },
    returned: { label: "Dikembalikan", severity: "secondary" },
    overdue: { label: "Telat", severity: "danger" },
  };
  return map[status] || { label: status, severity: "secondary" };
}

function formatSimpleDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString("id-ID", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

function getDateClass(date: Date): string {
  return isLoanDate(date) ? "loan-date" : "";
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
          <h3 class="text-xl font-bold text-surface-900 m-0">Detail Unit</h3>
          <p class="text-xs text-surface-500 font-medium uppercase tracking-wider" v-if="unit">{{ unit.code }}</p>
        </div>
      </div>
    </template>

    <div v-if="unit" class="p-6 space-y-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white border border-surface-200 shadow-sm rounded-2xl p-5 flex flex-col justify-between">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs font-bold text-surface-400 uppercase mb-1">Nama Alat / Tool</p>
              <h2 class="text-2xl font-extrabold text-surface-900">{{ unit.tool?.name || '-' }}</h2>
            </div>
            <Tag :value="statusLabel" :severity="statusSeverity" class="px-4 py-1 text-sm font-bold shadow-sm" />
          </div>
          
          <div class="mt-6 flex gap-6">
            <div>
              <p class="text-xs font-semibold text-surface-400 mb-1">Kode Unit</p>
              <p class="font-mono font-bold text-lg text-primary-700 bg-primary-50 px-2 py-1 rounded">{{ unit.code }}</p>
            </div>
            <div>
              <p class="text-xs font-semibold text-surface-400 mb-1">Terdaftar Sejak</p>
              <p class="text-sm font-medium text-surface-700">{{ formatDate(unit.created_at) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-surface-900 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
          <div class="relative z-10">
            <p class="text-xs font-bold text-surface-400 uppercase mb-3">Kondisi Terakhir</p>
            <div v-if="unit.latest_condition">
              <Tag 
                :value="getConditionLabel(unit.latest_condition.conditions)" 
                :severity="getConditionSeverity(unit.latest_condition.conditions)"
                class="mb-3"
              />
              <p class="text-sm opacity-90 line-clamp-2 italic">"{{ unit.latest_condition.notes || 'Tidak ada catatan' }}"</p>
              <div class="mt-4 pt-4 border-t border-surface-700">
                <p class="text-[10px] text-surface-400 uppercase font-bold">Terakhir diperiksa</p>
                <p class="text-xs font-medium">{{ formatDate(unit.latest_condition.recorded_at) }}</p>
              </div>
            </div>
            <div v-else class="py-4 text-center">
              <p class="text-xs text-surface-400">Belum ada data kondisi</p>
            </div>
          </div>
          <i class="pi pi-shield absolute -bottom-4 -right-4 text-8xl opacity-10"></i>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-5 gap-8">
        <div class="xl:col-span-2">
          <div class="flex items-center gap-2 mb-4">
            <i class="pi pi-calendar text-primary-600"></i>
            <h3 class="text-lg font-bold text-surface-800">Timeline Ketersediaan</h3>
          </div>
          <div class="bg-white border border-surface-200 rounded-2xl p-4 shadow-sm">
            <DatePicker
              v-model="selectedDate"
              inline
              class="modern-calendar w-full"
            />
            <div class="mt-4 flex items-center gap-4 px-2">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                <span class="text-xs font-bold text-surface-500 tracking-tight">DIPINJAM</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-surface-200 rounded-full"></span>
                <span class="text-xs font-bold text-surface-500 tracking-tight">TERSEDIA</span>
              </div>
            </div>
          </div>
        </div>

        <div class="xl:col-span-3 space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-surface-800 tracking-tight">Aktivitas Peminjaman</h3>
            <span class="text-xs bg-surface-100 px-3 py-1 rounded-full font-bold text-surface-500">
              Total: {{ props.loans.length }}
            </span>
          </div>

          <div v-if="hasLoans" class="max-h-[400px] overflow-y-auto pr-2 custom-scrollbar space-y-3">
             <div
              v-for="loan in props.loans"
              :key="loan.id"
              class="group relative bg-white border border-surface-200 rounded-xl p-4 transition-all hover:border-primary-300 hover:shadow-md"
              :class="{'border-l-4 border-l-blue-500 bg-blue-50/30': loan.status === 'active'}"
            >
              <div class="flex justify-between items-start">
                <div class="flex gap-3">
                   <div class="w-10 h-10 rounded-full bg-surface-100 flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                      <i class="pi pi-user text-surface-600 group-hover:text-primary-600"></i>
                   </div>
                   <div>
                     <p class="font-bold text-surface-900 leading-none mb-1">{{ loan.borrower_name }}</p>
                     <Tag :value="getLoanStatusBadge(loan.status).label" :severity="getLoanStatusBadge(loan.status).severity" class="text-[10px] uppercase font-black" />
                   </div>
                </div>
                <div class="text-right">
                  <p class="text-[10px] font-bold text-surface-400 uppercase tracking-widest mb-1">Periode</p>
                  <p class="text-xs font-bold text-surface-700">
                    {{ formatSimpleDate(loan.loan_date) }} - {{ formatSimpleDate(loan.due_date) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="flex flex-col items-center justify-center py-12 bg-surface-50 rounded-2xl border-2 border-dashed border-surface-200">
            <i class="pi pi-calendar-times text-4xl text-surface-300 mb-2"></i>
            <p class="text-surface-500 font-medium">Belum ada riwayat peminjaman</p>
          </div>
        </div>
      </div>

      <div class="pt-4">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-lg font-bold text-surface-800">Riwayat Kondisi Unit</h3>
            <p class="text-sm text-surface-500">Log perubahan status fisik unit secara berkala</p>
          </div>
          <Button
            label="Input Kondisi"
            icon="pi pi-plus"
            rounded
            class="shadow-lg hover:scale-105 transition-transform"
            severity="success"
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
                <span class="font-bold text-surface-800">{{ formatDate(data.recorded_at) }}</span>
                <span class="text-xs text-surface-400">{{ formatTime(data.recorded_at) }}</span>
              </div>
            </template>
          </Column>
          <Column header="Kondisi">
            <template #body="{ data }">
              <Tag :value="getConditionLabel(data.conditions)" :severity="getConditionSeverity(data.conditions)" rounded />
            </template>
          </Column>
          <Column header="Catatan / Keterangan" style="width: 40%">
            <template #body="{ data }">
              <div class="text-sm">
                <p class="text-surface-600 leading-relaxed">{{ data.notes || '-' }}</p>
                <div v-if="data.return_id" class="mt-1 flex items-center gap-1 text-[10px] font-bold text-primary-500 bg-primary-50 w-fit px-2 py-0.5 rounded">
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
        <Button label="Tutup Detail" icon="pi pi-times" text severity="secondary" @click="handleClose" class="font-bold" />
      </div>
    </template>
  </Dialog>
</template>

<style scoped>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

/* Modern Calendar Customization */
:deep(.modern-calendar) {
  border: none !important;
}

:deep(.modern-calendar .p-datepicker) {
  border: none;
  padding: 0;
}

:deep(.modern-calendar .p-datepicker-header) {
  background: transparent;
  color: #1e293b;
  padding: 0 0 1rem 0;
  border-bottom: 1px solid #f1f5f9;
}

:deep(.modern-calendar .p-datepicker-title) {
  font-weight: 800;
  font-size: 1.1rem;
}

:deep(.modern-calendar .p-datepicker-calendar) {
  margin-top: 1rem;
}

:deep(.modern-calendar .p-datepicker-calendar th) {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
}

:deep(.modern-calendar .p-datepicker-calendar td) {
  padding: 0.2rem;
}

:deep(.modern-calendar .p-datepicker-day) {
  width: 2.5rem !important;
  height: 2.5rem !important;
  border-radius: 12px !important;
  font-weight: 600;
  transition: all 0.2s;
}

:deep(.modern-calendar .p-datepicker-day.loan-date) {
  background: #3b82f6 !important;
  color: white !important;
  box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
}

:deep(.modern-calendar .p-datepicker-day:hover:not(.loan-date)) {
  background: #f1f5f9 !important;
}

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