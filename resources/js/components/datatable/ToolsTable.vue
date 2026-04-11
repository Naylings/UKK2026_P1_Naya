<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import type { Tool } from "@/types/tool";
import { useCategoryStore } from "@/stores/category";

// ── Props & Emits ─────────────────────────────────────────────────────────

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
  (e: "create"): void;
  (e: "view", tool: Tool): void;
  (e: "edit", tool: Tool): void;
  (e: "delete", tool: Tool): void;
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

// ── Category store untuk dropdown filter ──────────────────────────────────

const categoryStore = useCategoryStore();

onMounted(() => {
  if (!categoryStore.hasCategories) {
    categoryStore.fetchCategories();
  }
});

// ── State ─────────────────────────────────────────────────────────────────

const selectedRow = ref<Tool | null>(null);
const expandedRows = ref<any[]>([]);
const op = ref();

// ── Local filters (writable copy agar bisa diedit di template) ────────────

const localSearch = ref(props.filters?.search ?? "");
const localCategory = ref<number | "">(
  props.filters?.category ? Number(props.filters.category) : "",
);

// Debounce search
let searchTimeout: any = null;
watch(localSearch, (val) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    emit("update:filters", {
      search: val,
      category: localCategory.value !== "" ? String(localCategory.value) : "",
    });
  }, 500);
});

// Langsung emit saat kategori berubah (tidak perlu debounce)
watch(localCategory, (val) => {
  emit("update:filters", {
    search: localSearch.value,
    category: val !== "" ? String(val) : "",
  });
});

// Sync jika props.filters di-reset dari luar (clear filter)
watch(
  () => props.filters,
  (val) => {
    localSearch.value = val?.search ?? "";
    localCategory.value = val?.category ? Number(val.category) : "";
  },
  { deep: true },
);

// ── Helpers ───────────────────────────────────────────────────────────────

const categoryOptions = ref<{ label: string; value: number }[]>([]);
watch(
  () => categoryStore.categories,
  (cats) => {
    categoryOptions.value = cats.map((c) => ({ label: c.name, value: c.id }));
  },
  { immediate: true },
);

/**
 * Konversi path relatif storage Laravel ke URL yang bisa ditampilkan.
 * Menggunakan symlink storage (php artisan storage:link).
 * path: "tools/foto.jpg" → "/storage/tools/foto.jpg"
 */
function storageUrl(path: string | null): string | null {
  if (!path) return null;
  // Hindari double prefix jika path sudah lengkap
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

// ── Actions ───────────────────────────────────────────────────────────────

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
</script>

<template>
  <div>
    <!-- Filter Bar + Create Button -->
    <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
      <div class="flex flex-wrap gap-2 flex-1">
        <!-- Search -->
        <InputText
          v-model="localSearch"
          placeholder="Cari nama atau kode..."
          class="min-w-48 flex-1"
        />

        <!-- Filter Kategori -->
        <Select
          v-model="localCategory"
          :options="categoryOptions"
          option-label="label"
          option-value="value"
          placeholder="Semua Kategori"
          show-clear
          class="min-w-44"
        />

        <!-- Clear -->
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

      <Button icon="pi pi-plus" label="Tambah Tool" @click="emit('create')" />
    </div>

    <!-- DataTable -->
    <DataTable
      :value="tools"
      :loading="props.loading"
      v-model:expandedRows="expandedRows"
      paginator
      lazy
      :rows="props.perPage"
      :totalRecords="props.total"
      :first="(props.currentPage - 1) * props.perPage"
      paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
      :rows-per-page-options="[10, 25, 50]"
      @page="handlePageChange"
      class="text-sm"
    >
      <!-- Expand -->
      <Column expander style="width: 3rem" />

      <!-- Foto -->
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

      <!-- Nama + Kategori -->
      <Column header="Nama" field="name">
        <template #body="{ data }">
          <div>
            <div class="font-medium">{{ data.name }}</div>
            <div class="text-xs text-surface-400 mt-0.5">
              {{ data.category?.name ?? "—" }}
            </div>
          </div>
        </template>
      </Column>
      <!-- Deskripsi -->
      <Column header="Deskripsi">
        <template #body="{ data }">
          <span
            class="block overflow-hidden text-ellipsis whitespace-nowrap max-w-full"
            :title="data.description"
          >
            {{ data.description ?? "—" }}
          </span>
        </template>
      </Column>
      <!-- Kode -->
      <Column header="Kode" style="width: 1%; white-space: nowrap">
        <template #body="{ data }">
          <code
            class="text-xs bg-surface-100 px-2 py-1 rounded font-mono whitespace-nowrap"
          >
            {{ data.code_slug }}
          </code>
        </template>
      </Column>

      <!-- Tipe -->
      <Column header="Tipe" style="width: 8rem">
        <template #body="{ data }">
          <Tag
            :value="data.item_type"
            :severity="typeSeverity[data.item_type] ?? 'secondary'"
          />
        </template>
      </Column>

      <!-- Harga -->
      <Column header="Harga" style="width: 10rem">
        <template #body="{ data }">
          <span class="font-medium">{{ formatCurrency(data.price) }}</span>
        </template>
      </Column>

      <!-- Units -->
      <Column header="Units" style="width: 6rem" body-class="text-center">
        <template #body="{ data }">
          <Tag :value="String(data.units_count)" severity="secondary" rounded />
        </template>
      </Column>

      <!-- Actions -->
      <Column header="" style="width: 4rem" body-class="text-center">
        <template #body="{ data }">
          <Button
            icon="pi pi-ellipsis-v"
            text
            rounded
            size="small"
            @click="toggleMenu($event, data)"
          />
        </template>
      </Column>

      <!-- Expansion: bundle components -->
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

          <DataTable :value="data.bundle_components ?? []" class="text-sm">
            <!-- Foto komponen -->
            <Column header="Foto" style="width: 5rem">
              <template #body="{ data: comp }">
                <img
                  v-if="storageUrl(comp.tool?.photo_path)"
                  :src="storageUrl(comp.tool?.photo_path)!"
                  :alt="comp.tool?.name"
                  class="w-10 h-10 object-cover rounded-lg border border-surface-200"
                />
                <div
                  v-else
                  class="w-10 h-10 rounded-lg border border-surface-200 bg-surface-100 flex items-center justify-center"
                >
                  <i class="pi pi-image text-surface-400" />
                </div>
              </template>
            </Column>

            <Column header="Nama">
              <template #body="{ data: comp }">
                <div>
                  <div class="font-medium">{{ comp.tool?.name ?? "—" }}</div>
                  <div class="text-xs text-surface-400 font-mono mt-0.5">
                    {{ comp.tool?.code_slug }}
                  </div>
                </div>
              </template>
            </Column>

            <Column header="Qty" style="width: 5rem" body-class="text-center">
              <template #body="{ data: comp }">
                <Tag :value="String(comp.qty)" severity="secondary" rounded />
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
          @click="selectedRow && (emit('view', selectedRow), op.hide())"
        />
        <Button
          label="Edit"
          icon="pi pi-pencil"
          text
          severity="warning"
          size="small"
          class="justify-start"
          @click="selectedRow && (emit('edit', selectedRow), op.hide())"
        />
        <Divider class="my-1" />
        <Button
          label="Hapus"
          icon="pi pi-trash"
          text
          severity="danger"
          size="small"
          class="justify-start"
          @click="selectedRow && (emit('delete', selectedRow), op.hide())"
        />
      </div>
    </Popover>
  </div>
</template>
