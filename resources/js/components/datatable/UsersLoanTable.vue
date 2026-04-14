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
}>();

const emit = defineEmits<{
    (e: "update:filters", value: any): void;
    (e: "page-change", value: any): void;
    (e: "search", value: string): void;
    (e: "reset"): void;
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
            Riwayat Peminjaman
        </div>

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row gap-3 mb-5">
            <InputText
                v-model="globalFilter"
                placeholder="Cari tool / unit / tujuan..."
                class="w-full md:w-1/2"
            />

            <Select
                v-model="status"
                :options="statusOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Status"
                class="w-full md:w-48"
            />

            <Button
                label="Reset"
                icon="pi pi-refresh"
                severity="secondary"
                outlined
                @click="$emit('reset')"
            />
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
            <Column field="id" header="ID">
                <template #body="{ data }">
                    <span class="font-mono text-gray-500">
                        {{ data.id }}
                    </span>
                </template>
            </Column>

            <!-- TOOL -->
            <Column header="Alat">
                <template #body="{ data }">
                    {{ data.tool?.name }}
                </template>
            </Column>

            <!-- UNIT -->
            <Column header="Unit">
                <template #body="{ data }">
                    <Tag :value="data.unit?.code" severity="secondary" />
                </template>
            </Column>

            <!-- DATE -->
            <Column header="Tanggal">
                <template #body="{ data }">
                    <div class="text-xs">
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
            <Column header="Status">
                <template #body="{ data }">
                    <Tag
                        :value="data.status"
                        :severity="
                            data.status === 'active'
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

            <!-- PURPOSE -->
            <Column header="Tujuan">
                <template #body="{ data }">
                    <div class="line-clamp-2 text-gray-600">
                        {{ data.purpose }}
                    </div>
                </template>
            </Column>

            <template #empty>
                <div class="text-center py-10 text-gray-400">
                    Belum ada data peminjaman
                </div>
            </template>

            <template #loading>
                <div class="text-center py-6 text-gray-500">
                    Memuat data...
                </div>
            </template>
        </DataTable>
    </div>
</template>