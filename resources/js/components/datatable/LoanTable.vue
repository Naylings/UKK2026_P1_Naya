<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useFormatter } from "@/utils/useFormatter";

const { formatDate } = useFormatter();

const props = defineProps<{
    loans: any[];
    loading: boolean;
    meta: any;
    filters: any;
    statusOptions: Array<{ label: string; value: string }>;
    mode?: "user" | "petugas";
}>();

const emit = defineEmits<{
    (e: "update:filters", value: any): void;
    (e: "page-change", value: any): void;
    (e: "search", value: string): void;
    (e: "reset"): void;
    (e: "review", loan: any): void;
    (e: "detail", loan: any): void;
}>();

const globalFilter = ref("");

// sync status filter (biar tetap controlled dari parent)
const status = computed({
    get: () => props.filters.status,
    set: (val) => {
        emit("update:filters", {
            ...props.filters,
            status: val,
            page: 1,
        });
    },
});

// debounce search sederhana
let timeout: any;
watch(globalFilter, (val) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        emit("search", val);
    }, 300);
});
</script>

<template>
    <div class="card">
        <div class="font-semibold text-xl mb-4">
            {{
                props.mode === "petugas"
                    ? "Daftar Peminjaman"
                    : "Riwayat Peminjaman"
            }}
        </div>

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row md:items-end gap-3 mb-5">
            <!-- SEARCH -->
            <div class="w-full md:flex-1">
                <InputText
                    v-model="globalFilter"
                    placeholder="Cari alat / unit / tujuan..."
                    class="w-full"
                />
            </div>

            <!-- STATUS -->
            <div class="w-full md:w-56">
                <Select
                    v-model="status"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Status"
                    class="w-full"
                />
            </div>

            <!-- BUTTON -->
            <div class="w-full md:w-auto">
                <Button
                    label="Reset"
                    icon="pi pi-refresh"
                    severity="secondary"
                    outlined
                    class="w-full md:w-auto"
                    @click="$emit('reset')"
                />
            </div>
        </div>

        <!-- TABLE -->
        <DataTable
            :value="loans"
            :loading="loading"
            :rows="meta?.per_page || 10"
            :totalRecords="meta?.total || 0"
            paginator
            stripedRows
            showGridlines
            class="text-sm rounded-xl overflow-hidden"
            :first="((meta?.current_page || 1) - 1) * (meta?.per_page || 10)"
            @page="(e) => $emit('page-change', e)"
        >
            <!-- ID -->
            <Column header="No" class="w-auto">
                <template #body="{ index }">
                    {{
                        (meta?.current_page - 1) * (meta?.per_page || 10) +
                        index +
                        1
                    }}
                </template>
            </Column>

            <!-- PURPOSE (PINDAH KE KEDUA & FLEX) -->
            <Column header="Tujuan" class="w-full max-w-xl">
                <template #body="{ data }">
                    <div class="line-clamp-2 text-gray-600">
                        {{ data.purpose }}
                    </div>
                </template>
            </Column>

            <!-- TOOL -->
            <Column header="Alat" class="w-auto">
                <template #body="{ data }">
                    {{ data.tool?.name }}
                </template>
            </Column>

            <!-- UNIT -->
            <Column header="Unit" class="w-auto">
                <template #body="{ data }">
                    <Tag
                        :value="data.unit?.code"
                        severity="secondary"
                        class="whitespace-nowrap"
                    />
                </template>
            </Column>

            <!-- DATE -->
            <Column header="Tanggal" class="w-auto">
                <template #body="{ data }">
                    <div class="text-xs whitespace-nowrap">
                        <div class="font-medium">
                            {{ formatDate(data.loan_date) }}
                        </div>
                        <div class="text-gray-400">
                            → {{ formatDate(data.due_date) }}
                        </div>
                    </div>
                </template>
            </Column>

            <!-- STATUS -->
            <Column header="Status" class="w-auto">
                <template #body="{ data }">
                    <Tag
                        :value="data.status"
                        :severity="
data.status === 'approve'
                                ? 'success'
                                : data.status === 'pending'
                                  ? 'warning'
                                  : data.status === 'rejected'
                                    ? 'danger'
                                    : 'info'
                        "
                    />
                </template>
            </Column>

            <template #empty>
                <div class="text-center py-10 text-gray-400">
                    Belum ada data peminjaman
                </div>
            </template>

            <!-- ACTIONS -->
            <Column header="Aksi" class="w-28">
                <template #body="{ data }">
                    <div class="flex gap-1">
                        <Button
                            v-if="
                                data.status === 'pending' &&
                                props.mode === 'petugas'
                            "
                            icon="pi pi-check"
                            severity="warning"
                            text
                            rounded
                            v-tooltip="'Review'"
                            @click="$emit('review', data)"
                        />

                        <Button
                            icon="pi pi-eye"
                            severity="info"
                            text
                            rounded
                            v-tooltip="'Detail'"
                            @click="$emit('detail', data)"
                        />
                    </div>
                </template>
            </Column>

            <template #loading>
                <div class="text-center py-6 text-gray-500">Memuat data...</div>
            </template>
        </DataTable>
    </div>
</template>
