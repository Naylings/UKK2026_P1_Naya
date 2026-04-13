<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import type { Tool } from "@/types/tool";
import { useCategoryStore } from "@/stores/category";
import router from "@/router";

interface Props {
    tools: Tool[];
    loading?: boolean;
    currentPage?: number;
    lastPage?: number;
    total?: number;
    perPage?: number;
    filters?: { category: string; search: string };
}

interface Emits {
    (e: "view", tool: Tool): void;
    (e: "borrow", tool: Tool): void;
    (e: "clear-filter"): void;
    (e: "update:filters", filters: { category: string; search: string }): void;
    (e: "page-change", event: { page: number; rows: number }): void;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 10,
    filters: () => ({ category: "", search: "" }),
});

const emit = defineEmits<Emits>();

const categoryStore = useCategoryStore();

onMounted(() => {
    if (!categoryStore.hasCategories) {
        categoryStore.fetchCategories();
    }
});

const selectedRow = ref<Tool | null>(null);
const expandedRows = ref<any[]>([]);
const op = ref();

const localSearch = ref(props.filters?.search ?? "");
const localCategory = ref<number | "">(
    props.filters?.category ? Number(props.filters.category) : "",
);

let searchTimeout: any = null;
watch(localSearch, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        emit("update:filters", {
            search: val,
            category:
                localCategory.value !== "" ? String(localCategory.value) : "",
        });
    }, 500);
});

watch(localCategory, (val) => {
    emit("update:filters", {
        search: localSearch.value,
        category: val !== "" ? String(val) : "",
    });
});

watch(
    () => props.filters,
    (val) => {
        localSearch.value = val?.search ?? "";
        localCategory.value = val?.category ? Number(val.category) : "";
    },
    { deep: true },
);

const categoryOptions = ref<{ label: string; value: number }[]>([]);
watch(
    () => categoryStore.categories,
    (cats) => {
        categoryOptions.value = cats.map((c) => ({
            label: c.name,
            value: c.id,
        }));
    },
    { immediate: true },
);

function storageUrl(path: string | null): string | null {
    if (!path) return null;
    if (path.startsWith("http") || path.startsWith("/storage")) return path;
    return `/storage/${path}`;
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
}

const typeSeverity: Record<string, string> = {
    single: "success",
    bundle: "info",
    bundle_tool: "warning",
};

const toggleMenu = (event: Event, tool: Tool) => {
    selectedRow.value = tool;
    op.value.toggle(event);
};

const handlePageChange = (event: any) => {
    emit("page-change", { page: event.page + 1, rows: event.rows });
};

const hasActiveFilter = () =>
    localSearch.value !== "" || localCategory.value !== "";

const clearFilters = () => {
    localSearch.value = "";
    localCategory.value = "";
    emit("clear-filter");
};

function goToLoanRequest() {
    if (!selectedRow.value) return;

    router.push({
        name: "loan-request",
        params: {
            toolId: selectedRow.value.id,
        },
    });

    op.value?.hide();
}
</script>

<template>
    <div>
        <!-- Filter Bar — tombol Tambah dihapus -->
        <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
            <div class="flex flex-wrap gap-2 flex-1">
                <InputText
                    v-model="localSearch"
                    placeholder="Cari nama atau kode..."
                    class="min-w-48 flex-1"
                />
                <Select
                    v-model="localCategory"
                    :options="categoryOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="Semua Kategori"
                    class="min-w-44"
                />
                <Button
                    v-if="hasActiveFilter()"
                    icon="pi pi-times"
                    label="Reset"
                    severity="secondary"
                    outlined
                    size="small"
                    @click="clearFilters"
                />
            </div>
        </div>

        <!-- DataTable -->
        <DataTable
            :value="tools"
            :loading="props.loading"
            v-model:expandedRows="expandedRows"
            paginator
            lazy
            stripedRows
            showGridlines
            :rows="props.perPage"
            :totalRecords="props.total"
            :first="(props.currentPage - 1) * props.perPage"
            paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
            :rows-per-page-options="[10, 25, 50]"
            @page="handlePageChange"
            class="text-sm rounded-xl overflow-hidden"
        >
            <Column expander style="width: 3rem" />

            <Column header="Foto" style="width: 5rem">
                <template #body="{ data }">
                    <div class="flex justify-center">
                        <img
                            v-if="storageUrl(data.photo_path)"
                            :src="storageUrl(data.photo_path)!"
                            :alt="data.name"
                            class="w-12 h-12 object-cover rounded-lg border border-surface-200"
                        />
                        <div
                            v-else
                            class="w-12 h-12 rounded-lg border border-surface-200 bg-surface-100 flex items-center justify-center"
                        >
                            <i class="pi pi-image text-surface-400 text-lg" />
                        </div>
                    </div>
                </template>
            </Column>

            <Column header="Nama">
                <template #body="{ data }">
                    <div class="flex flex-col gap-0.5">
                        <span class="font-semibold">{{ data.name }}</span>
                        <span class="text-xs text-surface-400">
                            {{ data.category?.name ?? "Tanpa kategori" }}
                        </span>
                    </div>
                </template>
            </Column>

            <Column header="Deskripsi">
                <template #body="{ data }">
                    <p
                        class="text-surface-600 text-xs leading-snug line-clamp-2 max-w-xs"
                        :title="data.description"
                    >
                        {{ data.description ?? "-" }}
                    </p>
                </template>
            </Column>

            <Column header="Kode" style="width: 1%; white-space: nowrap">
                <template #body="{ data }">
                    <code
                        class="text-xs bg-surface-100 px-2 py-1 rounded font-mono whitespace-nowrap"
                    >
                        {{ data.code_slug }}
                    </code>
                </template>
            </Column>

            <Column header="Tipe" style="width: 7rem">
                <template #body="{ data }">
                    <Tag
                        :value="data.item_type.toUpperCase()"
                        :severity="typeSeverity[data.item_type] ?? 'secondary'"
                        rounded
                    />
                </template>
            </Column>

            <Column header="Harga" style="width: 10rem" body-class="text-right">
                <template #body="{ data }">
                    <span class="font-semibold">{{
                        formatCurrency(data.price)
                    }}</span>
                </template>
            </Column>

            <Column header="Unit" style="width: 5rem" body-class="text-center">
                <template #body="{ data }">
                    <span class="text-sm font-medium">{{
                        data.units_count
                    }}</span>
                </template>
            </Column>

            <Column header="" style="width: 3rem" body-class="text-center">
                <template #body="{ data }">
                    <Button
                        icon="pi pi-ellipsis-h"
                        text
                        rounded
                        size="small"
                        class="opacity-70 hover:opacity-100"
                        @click="toggleMenu($event, data)"
                    />
                </template>
            </Column>

            <template #expansion="{ data }">
                <div v-if="data.item_type === 'bundle'" class="p-4">
                    <p class="font-semibold mb-3 text-sm">
                        Komponen Bundle
                        <Tag
                            :value="String(data.bundle_components?.length ?? 0)"
                            severity="info"
                            rounded
                            class="ml-2"
                        />
                    </p>
                    <DataTable
                        :value="data.bundle_components ?? []"
                        class="text-sm"
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
                    </DataTable>
                </div>
                <div v-else class="p-4 text-surface-400 text-sm italic">
                    Tool ini bukan bundle — tidak ada komponen.
                </div>
            </template>

            <template #empty>
                <div class="text-center py-8 text-surface-400">
                    <i class="pi pi-inbox text-3xl mb-2 block" />
                    <span>Tidak ada data tool.</span>
                </div>
            </template>
        </DataTable>

        <!-- Overlay Menu -->
        <Popover ref="op">
            <div class="flex flex-col gap-1 min-w-40">
                <Button
                    label="Detail"
                    icon="pi pi-eye"
                    text
                    size="small"
                    class="justify-start"
                    @click="
                        selectedRow && (emit('view', selectedRow), op.hide())
                    "
                />
                <Divider class="my-1" />
                <Button
                    label="Ajukan Peminjaman"
                    icon="pi pi-send"
                    text
                    severity="info"
                    size="small"
                    class="justify-start"
                    @click="goToLoanRequest()"
                />
            </div>
        </Popover>
    </div>
</template>
