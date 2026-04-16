<script setup lang="ts">
import { ref, watch } from "vue";
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
    (e: "review", loan: any): void;
    (e: "detail", loan: any): void;
}>();

const globalFilter = ref("");

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
            Daftar Peminjaman
        </div>

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row gap-3 mb-5">
            <InputText v-model="globalFilter" placeholder="Cari..." />

            <Select
                :modelValue="filters.status"
                :options="statusOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Status"
                @update:modelValue="(val) => emit('update:filters', { ...filters, status: val, page: 1 })"
            />

            <Button label="Reset" outlined @click="$emit('reset')" />
        </div>

        <!-- TABLE -->
        <DataTable
            :value="loans"
            :loading="loading"
            paginator
            :rows="meta?.per_page || 10"
            :totalRecords="meta?.total || 0"
            :first="((meta?.current_page || 1) - 1) * (meta?.per_page || 10)"
            @page="(e) => $emit('page-change', e)"
        >
            <Column header="No">
                <template #body="{ index }">
                    {{
                        (meta?.current_page - 1) * (meta?.per_page || 10) +
                        index +
                        1
                    }}
                </template>
            </Column>

            <Column field="purpose" header="Tujuan" />

            <Column header="Alat">
                <template #body="{ data }">
                    {{ data.tool?.name }}
                </template>
            </Column>

            <Column header="Unit">
                <template #body="{ data }">
                    <Tag :value="data.unit?.code" />
                </template>
            </Column>

            <Column header="Tanggal">
                <template #body="{ data }">
                    {{ formatDate(data.loan_date) }} →
                    {{ formatDate(data.due_date) }}
                </template>
            </Column>

            <Column header="Status">
                <template #body="{ data }">
                    <Tag :value="data.status" />
                </template>
            </Column>

            <Column header="Aksi">
                <template #body="{ data }">
                    <Button
                        v-if="data.status === 'pending'"
                        icon="pi pi-check"
                        text
                        @click="$emit('review', data)"
                    />
                    <Button
                        icon="pi pi-eye"
                        text
                        @click="$emit('detail', data)"
                    />
                </template>
            </Column>
        </DataTable>
    </div>
</template>