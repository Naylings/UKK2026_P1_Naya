<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToolStore } from "@/stores/tool";
import { useToolUnitStore } from "@/stores/toolunit";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import type {
  ToolUnit,
  CreateToolUnitPayload,
  RecordConditionPayload,
} from "@/types/toolunit";
import type { LoanRecord } from "@/components/dialogs/details/UnitConditionHistoryModal.vue";
import ToolUnitsTable from "@/components/datatable/ToolUnitsTable.vue";
import ToolUnitFormModal from "@/components/dialogs/forms/ToolUnitFormModal.vue";
import UnitConditionHistoryModal from "@/components/dialogs/details/UnitConditionHistoryModal.vue";
import RecordConditionModal from "@/components/dialogs/forms/RecordConditionModal.vue";

const route = useRoute();
const router = useRouter();
const toolStore = useToolStore();
const unitStore = useToolUnitStore();
const toast = useToast();
const confirm = useConfirm();

// ── Computed ──────────────────────────────────────────────────────────────

const loading = computed(() => toolStore.loading);
const tool = computed(() => toolStore.currentTool);
const isBundle = computed(() => tool.value?.item_type === "bundle");
const canDelete = computed(() => tool.value?.can_delete ?? false);

// Unit Management
const units = computed(() => unitStore.toolUnits);
const unitsLoading = computed(() => unitStore.loading);
const unitsCurrentPage = computed(() => unitStore.currentPage);
const unitsLastPage = computed(() => unitStore.lastPage);
const unitsTotal = computed(() => unitStore.total);
const unitsPerPage = computed(() => unitStore.perPage);

// ── Modal States ────────────────────────────────────────────────────────

const showUnitFormModal = ref(false);

const showDetailModal = ref(false);
const selectedDetailUnit = ref<ToolUnit | null>(null);

const showRecordConditionModal = ref(false);
const selectedConditionUnit = ref<ToolUnit | null>(null);

// Loans Management
const selectedDetailUnitLoans = ref<LoanRecord[]>([]);
const loansLoading = ref(false);

// ── Lifecycle ─────────────────────────────────────────────────────────────

onMounted(async () => {
  const toolId = Number(route.params.id);

  if (!toolId || isNaN(toolId)) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "ID tool tidak valid.",
      life: 3000,
    });
    router.push({ name: "tool management" });
    return;
  }

  // Fetch tool detail
  const toolSuccess = await toolStore.fetchToolById(toolId);

  if (!toolSuccess) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: toolStore.error || "Tool tidak ditemukan.",
      life: 3000,
    });
    setTimeout(() => router.push({ name: "tool management" }), 1500);
  }

  // Fetch units untuk tool ini
  await fetchUnits();
});

// ── Unit Management Handlers ───────────────────────────────────────────────

async function fetchUnits(page: number = 1) {
  if (!tool.value?.id) return;

  const success = await unitStore.fetchUnits({
    tool_id: tool.value.id,
    page,
    per_page: unitsPerPage.value,
  });

  if (!success && unitStore.error) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: unitStore.error,
      life: 3000,
    });
  }
}

function openCreateUnitModal() {
  showUnitFormModal.value = true;
}

async function handleUnitFormSubmit(payload: CreateToolUnitPayload) {
  const quantity = payload.quantity ?? 1;
  const conditionLabel =
    payload.condition === "good"
      ? "Baik"
      : payload.condition === "broken"
        ? "Rusak"
        : "Maintenance";

  confirm.require({
    message: `Apakah Anda yakin ingin membuat ${quantity} unit baru dengan kondisi "${conditionLabel}"?`,
    header: "Konfirmasi Buat Unit",
    icon: "pi pi-exclamation-triangle",
    accept: async () => {
      const success = await unitStore.createUnit(payload);

      if (success) {
        toast.add({
          severity: "success",
          summary: "Berhasil",
          detail: unitStore.successMessage,
          life: 2000,
        });
        showUnitFormModal.value = false;
        await fetchUnits();
      } else {
        toast.add({
          severity: "error",
          summary: "Error",
          detail: unitStore.error,
          life: 3000,
        });
      }
    },
  });
}

function handleDeleteUnit(unit: ToolUnit) {
  // Check if unit has loans
  if (unit.has_loans) {
    toast.add({
      severity: "error",
      summary: "Gagal Menghapus",
      detail:
        "Unit tidak bisa dihapus karena masih memiliki history peminjaman",
      life: 3000,
    });
    return;
  }

  confirm.require({
    message: `Apakah Anda yakin ingin menghapus unit "${unit.code}"?`,
    header: "Konfirmasi Hapus Unit",
    icon: "pi pi-exclamation-triangle",
    accept: async () => {
      const success = await unitStore.deleteUnit(unit.code);

      if (success) {
        toast.add({
          severity: "success",
          summary: "Berhasil",
          detail: unitStore.successMessage,
          life: 2000,
        });
        await fetchUnits();
      } else {
        toast.add({
          severity: "error",
          summary: "Error",
          detail: unitStore.error,
          life: 3000,
        });
      }
    },
  });
}

function openDetailModal(unit: ToolUnit) {
  selectedDetailUnit.value = unit;
  showDetailModal.value = true;
  handleLoadLoans(unit);
  handleLoadConditionHistory();
}

async function handleLoadLoans(unit: ToolUnit) {
  loansLoading.value = true;
  try {
    // TODO: Fetch loans untuk unit ini dari API
    // Untuk saat ini, set empty array sebagai placeholder
    selectedDetailUnitLoans.value = [];
  } catch (err) {
    console.error("Error loading loans:", err);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Gagal memuat data peminjaman.",
      life: 3000,
    });
  } finally {
    loansLoading.value = false;
  }
}

async function handleLoadConditionHistory() {
  if (!selectedDetailUnit.value) return;

  const success = await unitStore.fetchConditionHistory(
    selectedDetailUnit.value.code,
  );

  if (!success && unitStore.error) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: unitStore.error,
      life: 3000,
    });
  }
}

function openRecordConditionModal(unit: ToolUnit) {
  selectedConditionUnit.value = unit;
  showRecordConditionModal.value = true;
  showDetailModal.value = false;
}

function handleRecordCondition(payload: RecordConditionPayload) {
  if (!selectedConditionUnit.value) return;

  const conditionLabel =
    payload.condition === "good"
      ? "Baik"
      : payload.condition === "broken"
        ? "Rusak"
        : "Maintenance";

  confirm.require({
    message: `Apakah Anda yakin ingin mencatat kondisi "${conditionLabel}" untuk unit "${selectedConditionUnit.value.code}"?`,
    header: "Konfirmasi Catat Kondisi",
    icon: "pi pi-exclamation-triangle",
    accept: async () => {
      const success = await unitStore.recordCondition(
        selectedConditionUnit.value!.code,
        payload,
      );

      if (success) {
        toast.add({
          severity: "success",
          summary: "Berhasil",
          detail: "Kondisi unit berhasil dicatat.",
          life: 2000,
        });
        showRecordConditionModal.value = false;

        // Reload condition history jika detail modal masih terbuka
        if (
          selectedDetailUnit.value?.code === selectedConditionUnit.value.code
        ) {
          await handleLoadConditionHistory();
        }

        // Reload units list
        await fetchUnits(unitsCurrentPage.value);
      } else {
        toast.add({
          severity: "error",
          summary: "Error",
          detail: unitStore.error,
          life: 3000,
        });
      }
    },
  });
}

// ── Tool Management Handlers ──────────────────────────────────────────────

function goBack() {
  router.push({ name: "tool management" });
}

function handleEdit() {
  if (!tool.value) return;
  goBack();
}

function confirmDelete() {
  if (!tool.value) return;

  if (!canDelete.value) {
    const reasons: string[] = [];
    if (tool.value.has_units) reasons.push("masih memiliki unit fisik");
    if (tool.value.has_loans) reasons.push("masih memiliki peminjaman");
    if (tool.value.has_bundles)
      reasons.push("masih menjadi bagian dari bundle");

    toast.add({
      severity: "warn",
      summary: "Tidak Dapat Dihapus",
      detail: `Tool "${tool.value.name}" ${reasons.join(", ")}.`,
      life: 4000,
    });
    return;
  }

  confirm.require({
    message: `Apakah Anda yakin ingin menghapus tool "${tool.value.name}"?`,
    header: "Konfirmasi Hapus",
    icon: "pi pi-exclamation-triangle",
    accept: async () => {
      const success = await toolStore.deleteTool(tool.value!.id);
      if (success) {
        toast.add({
          severity: "success",
          summary: "Berhasil",
          detail: toolStore.successMessage,
          life: 2000,
        });
        setTimeout(() => router.push({ name: "tool management" }), 2000);
      } else {
        toast.add({
          severity: "error",
          summary: "Error",
          detail: toolStore.error,
          life: 3000,
        });
      }
    },
  });
}

// ── Helpers ───────────────────────────────────────────────────────────────

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
                <i class="pi pi-image text-surface-400 text-4xl block mb-2" />
                <p class="text-sm text-surface-400">Tidak ada foto</p>
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
              <p class="text-lg mt-1">{{ tool.category?.name ?? "—" }}</p>
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
              <p class="text-lg mt-1">{{ tool.min_credit_score ?? "—" }}</p>
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
            <p class="text-sm text-surface-600 mt-1 leading-relaxed">
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
          <p class="text-3xl font-bold mt-2">{{ tool.units_count ?? 0 }}</p>
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

            <Column header="Subtotal" style="width: 12rem">
              <template #body="{ data: comp }">
                <span class="font-semibold">
                  {{
                    formatCurrency((comp.tool?.price ?? 0) * (comp.qty ?? 1))
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
          @create="openCreateUnitModal"
          @delete="handleDeleteUnit"
          @view-detail="openDetailModal"
          @record-condition="
            (unit) => {
              selectedConditionUnit = unit;
              showRecordConditionModal = true;
            }
          "
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
        <Button
          label="Edit"
          icon="pi pi-pencil"
          severity="warning"
          @click="handleEdit"
        />
        <Button
          label="Hapus"
          icon="pi pi-trash"
          severity="danger"
          :disabled="!canDelete"
          @click="confirmDelete"
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
    @submit="handleUnitFormSubmit"
  />

  <!-- Unit Detail & Condition History Modal -->
  <UnitConditionHistoryModal
    :visible="showDetailModal"
    :unit="selectedDetailUnit"
    :condition-history="unitStore.conditionHistory"
    :loans="selectedDetailUnitLoans"
    :loading="unitsLoading || loansLoading"
    @update:visible="showDetailModal = $event"
    @record-condition="
      selectedDetailUnit && openRecordConditionModal(selectedDetailUnit)
    "
    @load-history="handleLoadConditionHistory"
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
