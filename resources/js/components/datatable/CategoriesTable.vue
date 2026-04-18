<script setup lang="ts">
import { computed, ref, watch } from "vue";
import type { Category } from "@/types/category";

interface Props {
    categories: Category[];
    loading?: boolean;
    currentPage?: number;
    lastPage?: number;
    total?: number;
    perPage?: number;
    filters?: { search: string };
}

interface Emits {
    (e: "create"): void;
    (e: "edit", category: Category): void;
    (e: "delete", category: Category): void;
    (e: "clear-filter"): void;
    (e: "update:filters", filters: { search: string }): void;
    (e: "page-change", event: { page: number; rows: number }): void;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 10,
    filters: () => ({ search: "" }),
});

const emit = defineEmits<Emits>();

const localFilters = computed({
    get: () => props.filters,
    set: (val) => emit("update:filters", val),
});
const localCurrentPage = ref(props.currentPage);
let timeout: any = null;

watch(
    () => props.currentPage,
    (val) => (localCurrentPage.value = val),
);

watch(
    () => localFilters.value,
    (val) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => emit("update:filters", val), 500);
    },
    { deep: true },
);

const handlePageChange = (event: any) => {
    emit("page-change", { page: event.page + 1, rows: event.rows });
};
</script>

<template>
    <div class="card">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex flex-1 gap-2 min-w-75">
                <InputText
                    v-model="localFilters.search"
                    placeholder="Cari nama kategori..."
                    class="flex-1"
                    clearable
                />
                <Button
                    v-if="localFilters.search"
                    icon="pi pi-times"
                    text
                    severity="secondary"
                    class="self-center"
                    @click="emit('clear-filter')"
                    title="Hapus filter"
                />
            </div>

            <Button
                icon="pi pi-plus"
                label="Tambah Kategori"
                class="mt-2 sm:mt-0"
                @click="emit('create')"
            />
        </div>

        <DataTable
            lazy
            :value="categories"
            :loading="props.loading"
            paginator
            :first="(props.currentPage - 1) * props.perPage"
            :rows="props.perPage"
            :rowsPerPageOptions="[5, 10, 20, 50]"
            :totalRecords="props.total"
            tableStyle="min-width: 50rem"
            @page="handlePageChange"
            @update:rows="
                emit('page-change', { page: props.currentPage, rows: $event })
            "
        >
            <Column field="name" header="Nama" style="width: 20%" />
            <Column field="description" header="Deskripsi" style="width: 80%" />

            <Column
                header="Actions"
                style="width: 16rem"
                body-class="text-center"
            >
                <template #body="{ data }">
                    <div class="flex items-center justify-center gap-2">
                        <Button
                            label="Edit"
                            icon="pi pi-pencil"
                            text
                            severity="warning"
                            @click="emit('edit', data)"
                        />
                        <Button
                            label="Hapus"
                            icon="pi pi-trash"
                            text
                            severity="danger"
                            @click="emit('delete', data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
