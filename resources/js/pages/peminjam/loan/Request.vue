<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import { useLoanRequest } from "./composables/useLoanRequest";
import { useToolUnits } from "@/pages/admin/tools/composable/useToolUnits";

import type { ToolUnit } from "@/types/toolunit";

import UnitConditionHistoryModal from "@/components/dialogs/details/UnitConditionHistoryModal.vue";

const route = useRoute();
const router = useRouter();

// ── Loan composable (domain utama)
const {
  toolId,
  dueDate,
  loanDate,
  reason,
  availableUnits,
  selectedUnit,
  loading,
  searching,
  error,
  tool,
  searchAvailableUnits,
  clearSearch,
  selectUnit,
  submitLoan,
  setToolId,
  loadTool,
} = useLoanRequest();

// ── Tool unit domain (history only)
const toolUnits = useToolUnits(computed(() => toolId.value));
const { conditionHistory, loadConditionHistory, loading: historyLoading } =
  toolUnits;

// ── UI state
const detailVisible = ref(false);
const selectedDetailUnit = ref<ToolUnit | null>(null);

// ── init
onMounted(async () => {
  const id = Number(route.params.toolId);

  if (!id) {
    router.push({ name: "peminjam.tools" });
    return;
  }

  setToolId(id);
  await loadTool(id);
});

// ── validation
const isInvalidDateRange = computed(() => {
  if (!loanDate.value || !dueDate.value) return true;
  return new Date(loanDate.value) > new Date(dueDate.value);
});

// ── actions
async function openDetail(unit: ToolUnit) {
  selectedDetailUnit.value = unit;
  detailVisible.value = true;

  if (unit.code) {
    await loadConditionHistory(unit.code);
  }
}
</script>

<template>
    <div class="max-w-5xl mx-auto">
        <div class="card space-y-8">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Button
                    icon="pi pi-arrow-left"
                    text
                    rounded
                    @click="router.back()"
                />
                <div>
                    <h1 class="text-2xl font-bold">Ajukan Peminjaman</h1>
                    <p class="text-sm text-gray-500">
                        Isi data peminjaman dengan lengkap
                    </p>
                </div>
            </div>

            <!-- Tool Info -->
            <div
                v-if="!loading"
                class="p-5 rounded-2xl bg-linear-to-r from-blue-50 to-indigo-50 border border-blue-100 flex items-center gap-4"
            >
                <div class="p-3 bg-blue-100 rounded-xl">
                    <i class="pi pi-box text-xl text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">
                        Peminjaman {{ tool?.name ?? "Loading..." }}
                    </h2>

                    <p class="text-sm text-gray-600">
                        Kategori: {{ tool?.category?.name ?? "-" }}
                    </p>
                </div>
            </div>

            <!-- FORM SECTION -->
            <div class="space-y-6">
                <!-- Date Section -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border space-y-5"
                >
                    <h3 class="font-semibold text-gray-800">
                        Periode Peminjaman
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="text-sm font-medium text-gray-600 mb-2 block"
                            >
                                Tanggal Pinjam
                            </label>
                            <Datepicker
                                v-model="loanDate"
                                date-format="dd/mm/yy"
                                :show-icon="true"
                                class="w-full"
                                :min-date="new Date()"
                            />
                        </div>

                        <div>
                            <label
                                class="text-sm font-medium text-gray-600 mb-2 block"
                            >
                                Tanggal Kembali
                            </label>
                            <Datepicker
                                v-model="dueDate"
                                date-format="dd/mm/yy"
                                :show-icon="true"
                                class="w-full"
                                :min-date="new Date()"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <Button
                            label="Cari Unit Tersedia"
                            icon="pi pi-search"
                            :loading="searching"
                            :disabled="isInvalidDateRange"
                            severity="info"
                            @click="searchAvailableUnits"
                        />
                    </div>
                </div>

                <!-- Reason -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border">
                    <label
                        class="text-sm font-semibold text-gray-700 block mb-3"
                    >
                        Alasan Peminjaman <span class="text-red-500">*</span>
                    </label>

                    <Textarea
                        v-model="reason"
                        rows="4"
                        auto-resize
                        class="w-full"
                        placeholder="Jelaskan keperluan peminjaman..."
                        :disabled="searching"
                        :maxlength="500"
                    />

                    <div class="text-xs text-gray-400 mt-2 text-right">
                        {{ reason.length }}/500
                    </div>
                </div>

                <!-- Error -->
                <Message v-if="error" severity="error" class="w-full">
                    {{ error }}
                </Message>

                <!-- Results -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border">
                    <div class="flex items-center justify-between mb-4">
                        <div class="font-semibold text-gray-800">
                            Unit Tersedia
                            <Badge
                                :value="availableUnits.length"
                                class="ml-2"
                            />
                        </div>

                        <Button
                            v-if="availableUnits.length"
                            label="Reset"
                            text
                            severity="secondary"
                            @click="clearSearch"
                        />
                    </div>

                    <DataTable
                        :value="availableUnits"
                        striped-rows
                        responsive-layout="scroll"
                        class="rounded-xl overflow-hidden"
                        :row-class="
                            (row) =>
                                row.code === selectedUnit?.code
                                    ? 'bg-blue-50'
                                    : ''
                        "
                    >
                        <Column field="code" header="Kode">
                            <template #body="slotProps">
                                <div class="font-mono font-bold">
                                    {{ slotProps.data.code }}
                                </div>
                            </template>
                        </Column>

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag
                                    :value="
                                        slotProps.data.status === 'available'
                                            ? 'Tersedia'
                                            : slotProps.data.status
                                    "
                                    :severity="
                                        slotProps.data.status === 'available'
                                            ? 'success'
                                            : 'warning'
                                    "
                                />
                            </template>
                        </Column>

                        <Column header="Kondisi">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.latest_condition"
                                    :value="
                                        slotProps.data.latest_condition
                                            .conditions
                                    "
                                    severity="success"
                                />
                                <span v-else class="text-gray-400 text-sm"
                                    >-</span
                                >
                            </template>
                        </Column>

                        <Column field="availability_reason" header="Info">
                            <template #body="slotProps">
                                <span class="text-sm text-gray-600">
                                    {{ slotProps.data.availability_reason }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Aksi">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button
                                        icon="pi pi-eye"
                                        size="small"
                                        severity="info"
                                        text
                                        v-tooltip="'Lihat Detail'"
                                        @click="openDetail(slotProps.data)"
                                    />

                                    <Button
                                        label="Pilih"
                                        size="small"
                                        :severity="
                                            selectedUnit?.code ===
                                            slotProps.data.code
                                                ? 'success'
                                                : 'secondary'
                                        "
                                        @click="selectUnit(slotProps.data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between pt-4">
                <div
                    v-if="selectedUnit"
                    class="bg-green-100 border border-green-200 text-green-800 px-4 py-2 rounded-lg"
                >
                    ✔ Unit dipilih: <b>{{ selectedUnit.code }}</b>
                </div>

                <div class="flex gap-2">
                    <Button
                        label="Kembali"
                        severity="secondary"
                        outlined
                        @click="router.back()"
                    />

                    <Button
                        label="Ajukan"
                        icon="pi pi-send"
                        severity="info"
                        :loading="loading"
                        :disabled="!selectedUnit || !reason.trim()"
                        @click="submitLoan"
                    />
                </div>
            </div>
        </div>

        <UnitConditionHistoryModal
            v-model:visible="detailVisible"
            :unit="selectedDetailUnit"
            :tool-name="tool?.name"
            :condition-history="conditionHistory"
            :loans="[]"
            :mode="'loan'"
            :loading="loading"
        />
    </div>
</template>

