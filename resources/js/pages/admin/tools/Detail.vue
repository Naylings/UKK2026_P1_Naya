<script setup lang="ts">
import { computed, onMounted } from "vue";
import { useToolDetail } from "./composable/useToolDetail";
import { useToolUnits } from "./composable/useToolUnits";
import { useFormatter } from "@/utils/useFormatter";
import { useUnitUIState } from "./composable/useUnitUIState";

const { storageUrl, formatCurrency } = useFormatter();
const { tool, loading, isBundle, canDelete, init, confirmDelete, goBack } =
    useToolDetail();

const {
    units,
    loading: unitsLoading,
    currentPage: unitsCurrentPage,
    lastPage: unitsLastPage,
    total: unitsTotal,
    perPage: unitsPerPage,
    conditionHistory,
    loadConditionHistory,
    fetchUnits,
    deleteUnit,
    createUnit,
    recordCondition,
} = useToolUnits(computed(() => tool.value?.id));

import { watch } from "vue";

const {
    showUnitFormModal,
    showDetailModal,
    showRecordConditionModal,
    selectedDetailUnit,
    selectedConditionUnit,
    openCreateModal,
    openDetail,
    openRecordCondition,
} = useUnitUIState();

onMounted(() => {
    init(fetchUnits);
});

// Auto-fetch unit condition history when detail modal opens
watch(showDetailModal, async (newVal) => {
    if (newVal && selectedDetailUnit.value) {
        await loadConditionHistory(selectedDetailUnit.value.code);
    }
});


async function handleRecordCondition(payload: any) {
    if (!selectedConditionUnit.value) return;

    const success = await recordCondition(
        selectedConditionUnit.value.code,
        payload
    );

    if (success) {
        showRecordConditionModal.value = false; 
    }
}

async function handleCreateUnit(payload: any) {
    const success = await createUnit(payload);

    if (success) {
        showUnitFormModal.value = false; 
    }
}
</script>

<template>
    <div class="card">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-6">
            <Button
                icon="pi pi-arrow-left"
                text
                rounded
                size="large"
                @click="goBack"
                class="opacity-70 hover:opacity-100"
            />
            <h1 class="text-2xl font-bold">Detail Tool</h1>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-12">
            <ProgressSpinner />
        </div>

        <!-- Content -->
        <div v-else-if="tool" class="space-y-6">
            <!-- Main Info Card -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Photo -->
                <div class="lg:col-span-1">
                    <div
                        class="rounded-xl overflow-hidden border border-surface-200 bg-surface-50 aspect-square"
                    >
                        <img
                            v-if="storageUrl(tool.photo_path)"
                            :src="storageUrl(tool.photo_path)!"
                            :alt="tool.name"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-surface-100"
                        >
                            <div class="text-center">
                                <i
                                    class="pi pi-image text-surface-400 text-4xl block mb-2"
                                />
                                <p class="text-sm text-surface-400">
                                    Tidak ada foto
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Name & Category -->
                    <div>
                        <label
                            class="text-xs font-semibold text-surface-500 uppercase tracking-wide"
                        >
                            Nama Tool
                        </label>
                        <p class="text-2xl font-bold mt-1">{{ tool.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Kategori -->
                        <div>
                            <label
                                class="text-xs font-semibold text-surface-500 uppercase tracking-wide"
                            >
                                Kategori
                            </label>
                            <p class="text-lg mt-1">
                                {{ tool.category?.name ?? "—" }}
                            </p>
                        </div>

                        <!-- Tipe -->
                        <div>
                            <label
                                class="text-xs font-semibold text-surface-500 uppercase tracking-wide"
                            >
                                Tipe
                            </label>
                            <Tag
                                :value="tool.item_type.toUpperCase()"
                                :severity="
                                    tool.item_type === 'single'
                                        ? 'success'
                                        : tool.item_type === 'bundle'
                                          ? 'info'
                                          : 'warning'
                                "
                                class="mt-1"
                                rounded
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Harga -->
                        <div>
                            <label
                                class="text-xs font-semibold text-surface-500 uppercase tracking-wide"
                            >
                                Harga
                            </label>
                            <p class="text-lg font-semibold mt-1">
                                {{ formatCurrency(tool.price) }}
                            </p>
                        </div>

                        <!-- Min Credit Score -->
                        <div>
                            <label
                                class="text-xs font-semibold text-surface-500 uppercase tracking-wide"
                            >
                                Min. Skor Kredit
                            </label>
                            <p class="text-lg mt-1">
                                {{ tool.min_credit_score ?? "—" }}
                            </p>
                        </div>
                    </div>

                    <!-- Kode Slug -->
                    <div>
                        <label
                            class="text-xs font-semibold text-surface-500 uppercase tracking-wide"
                        >
                            Kode
                        </label>
                        <code
                            class="block mt-1 bg-surface-100 px-3 py-2 rounded font-mono text-sm"
                        >
                            {{ tool.code_slug }}
                        </code>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label
                            class="text-xs font-semibold text-surface-500 uppercase tracking-wide"
                        >
                            Deskripsi
                        </label>
                        <p
                            class="text-sm text-surface-600 mt-1 leading-relaxed"
                        >
                            {{ tool.description ?? "—" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Units Info Summary -->
            <Divider />
            <div class="grid grid-cols-4 gap-4">
                <div class="border border-surface-200 rounded-lg p-4">
                    <p
                        class="text-xs text-surface-500 font-semibold uppercase tracking-wide"
                    >
                        Total Units
                    </p>
                    <p class="text-3xl font-bold mt-2">
                        {{ tool.units_count ?? 0 }}
                    </p>
                </div>
                <div class="border border-surface-200 rounded-lg p-4">
                    <p
                        class="text-xs text-surface-500 font-semibold uppercase tracking-wide"
                    >
                        Tersedia
                    </p>
                    <p class="text-3xl font-bold mt-2 text-green-600">
                        {{ tool.available_units ?? 0 }}
                    </p>
                </div>
                <div class="border border-surface-200 rounded-lg p-4">
                    <p
                        class="text-xs text-surface-500 font-semibold uppercase tracking-wide"
                    >
                        Dipinjam
                    </p>
                    <p class="text-3xl font-bold mt-2 text-orange-600">
                        {{ tool.borrowed_units ?? 0 }}
                    </p>
                </div>
                <div class="border border-surface-200 rounded-lg p-4">
                    <p
                        class="text-xs text-surface-500 font-semibold uppercase tracking-wide"
                    >
                        Nonaktif
                    </p>
                    <p class="text-3xl font-bold mt-2 text-red-600">
                        {{ tool.nonactive_units ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- Bundle Components -->
            <div v-if="isBundle" class="space-y-4">
                <Divider />
                <div>
                    <h3 class="text-lg font-semibold mb-4">
                        Komponen Bundle
                        <Tag
                            :value="String(tool.bundle_components?.length ?? 0)"
                            severity="info"
                            rounded
                            class="ml-2"
                        />
                    </h3>

                    <DataTable
                        :value="tool.bundle_components ?? []"
                        class="text-sm"
                        striped-rows
                    >
                        <Column header="Nama">
                            <template #body="{ data: comp }">
                                <div>
                                    <div class="font-medium">
                                        {{ comp.tool?.name ?? "—" }}
                                    </div>
                                    <div
                                        class="text-xs text-surface-400 font-mono mt-0.5"
                                    >
                                        {{ comp.tool?.code_slug }}
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column
                            header="Qty"
                            style="width: 5rem"
                            body-class="text-center"
                        >
                            <template #body="{ data: comp }">
                                <Tag
                                    :value="String(comp.qty)"
                                    severity="secondary"
                                    rounded
                                />
                            </template>
                        </Column>

                        <Column header="Harga" style="width: 10rem">
                            <template #body="{ data: comp }">
                                {{ formatCurrency(comp.tool?.price ?? 0) }}
                            </template>
                        </Column>

                        <Column header="Subtotal" style="width: 12rem">
                            <template #body="{ data: comp }">
                                <span class="font-semibold">
                                    {{
                                        formatCurrency(
                                            (comp.tool?.price ?? 0) *
                                                (comp.qty ?? 1),
                                        )
                                    }}
                                </span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>

            <!-- Unit Management Section -->
            <Divider />
            <div class="space-y-4">
                <ToolUnitsTable
                    :units="units"
                    :loading="unitsLoading"
                    :current-page="unitsCurrentPage"
                    :last-page="unitsLastPage"
                    :total="unitsTotal"
                    :per-page="unitsPerPage"
                    :tool-id="tool.id"
                    @create="openCreateModal"
                    @delete="deleteUnit"
                    @view-detail="openDetail"
                    @record-condition="openRecordCondition"
                    @page-change="(event) => fetchUnits(event.page)"
                />
            </div>

            <!-- Tool Actions -->
            <Divider />
            <div class="flex gap-3 justify-end">
                <Button
                    label="Kembali"
                    icon="pi pi-arrow-left"
                    severity="secondary"
                    outlined
                    @click="goBack"
                />
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 text-surface-400">
            <i class="pi pi-inbox text-4xl mb-3 block" />
            <p>Tool tidak ditemukan</p>
        </div>
    </div>

    <!-- Unit Modals -->

    <!-- Create/Edit Unit Modal -->
    <ToolUnitFormModal
        :visible="showUnitFormModal"
        :tool-id="tool?.id"
        :loading="unitsLoading"
        @update:visible="showUnitFormModal = $event"
        @submit="handleCreateUnit"
    />

    <!-- Unit Detail & Condition History Modal -->
    <UnitConditionHistoryModal
        :visible="showDetailModal"
        :unit="selectedDetailUnit"
        :condition-history="conditionHistory"
        :loans="[]"
        :loading="unitsLoading"
        @update:visible="showDetailModal = $event"
        @record-condition="
            selectedDetailUnit && openRecordCondition(selectedDetailUnit)
        "
        @load-history="
            selectedDetailUnit && loadConditionHistory(selectedDetailUnit.code)
        "
        :mode="'admin'" 
    />

    <!-- Record Condition Modal -->
    <RecordConditionModal
        :visible="showRecordConditionModal"
        :unit-code="selectedConditionUnit?.code"
        :loading="unitsLoading"
        @update:visible="showRecordConditionModal = $event"
        @submit="handleRecordCondition"
    />
</template>
