<script setup lang="ts">
import { computed, watch, ref } from "vue";
import type { ItemType, BundleComponentPayload } from "@/types/tool";
import type { ToolFormData } from "@/pages/admin/tools/composable/useToolManagement";
import { useCategoryStore } from "@/stores/category";

// ── Props & Emits ─────────────────────────────────────────────────────────

interface Props {
  visible: boolean;
  loading?: boolean;
  isEditMode?: boolean;
  form: ToolFormData;
  dialogTitle: string;
  submitButtonLabel: string;
  isBundle: boolean;
  bundleComponentsCount: number;
  photoPreview: string | null;
}

interface Emits {
  (e: "update:visible", value: boolean): void;
  (e: "update:form", value: ToolFormData): void;
  (e: "submit"): void;
  (e: "cancel"): void;
  (e: "item-type-change", value: ItemType): void;
  (e: "open-bundle-component-modal", index?: number): void;
  (e: "remove-bundle-component", index: number): void;
  (e: "photo-selected", event: Event): void;
  (e: "remove-photo"): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  isEditMode: false,
  photoPreview: null,
});

const emit = defineEmits<Emits>();

// ── Category store ────────────────────────────────────────────────────────

const categoryStore = useCategoryStore();

const categoryOptions = computed(() =>
  categoryStore.categories.map((c) => ({ label: c.name, value: c.id })),
);

// Pastikan categories ter-load
if (!categoryStore.hasCategories) {
  categoryStore.fetchCategories();
}

// ── Local form state ──────────────────────────────────────────────────────
//
// PENYEBAB BUG kategori tidak terpilih saat edit:
// Select PrimeVue mencocokkan v-model dengan option-value via ===.
// Jika categories belum ter-load saat form dibuka, Select tidak bisa
// menemukan option yang cocok, lalu setelah categories masuk,
// binding tidak ter-refresh otomatis.
//
// Solusi: pakai local ref yang disync dua arah via watch.
// Ini juga menghindari mutasi prop langsung (Vue 3 best practice).
//

const localName = ref(props.form.name);
const localCategoryId = ref<number | null>(props.form.category_id);
const localItemType = ref(props.form.item_type);
const localPrice = ref(props.form.price);
const localMinCredit = ref(props.form.min_credit_score);
const localCodeSlug = ref(props.form.code_slug);
const localDescription = ref(props.form.description);

// Sync ke bawah: saat form prop berubah (dialog dibuka dengan data baru)
watch(
  () => props.form,
  (newForm) => {
    localName.value = newForm.name;
    localCategoryId.value = newForm.category_id;
    localItemType.value = newForm.item_type;
    localPrice.value = newForm.price;
    localMinCredit.value = newForm.min_credit_score;
    localCodeSlug.value = newForm.code_slug;
    localDescription.value = newForm.description;
  },
  { deep: true, immediate: true },
);

// Sync ke atas: saat local state berubah, emit update:form
// Ini agar composable di parent tetap punya data terkini
function emitFormUpdate() {
  emit("update:form", {
    ...props.form,
    name: localName.value,
    category_id: localCategoryId.value,
    item_type: localItemType.value,
    price: localPrice.value,
    min_credit_score: localMinCredit.value,
    code_slug: localCodeSlug.value,
    description: localDescription.value,
  });
}

watch(localName, emitFormUpdate);
watch(localCategoryId, emitFormUpdate);
watch(localPrice, emitFormUpdate);
watch(localMinCredit, emitFormUpdate);
watch(localCodeSlug, emitFormUpdate);
watch(localDescription, emitFormUpdate);

// item_type punya handler tersendiri
function onItemTypeChange(value: ItemType) {
  localItemType.value = value;
  emit("item-type-change", value);
}

// ── Storage URL helper ────────────────────────────────────────────────────
//
// Laravel storage:link membuat symlink public/storage → storage/app/public.
// Path dari BE: "tools/foto.jpg" → URL: "/storage/tools/foto.jpg"
//
function storageUrl(path: string | null): string | null {
  if (!path) return null;
  if (
    path.startsWith("http") ||
    path.startsWith("/storage") ||
    path.startsWith("blob:")
  )
    return path;
  return `/storage/${path}`;
}

// photoPreview bisa berupa:
// - blob: URL  → file baru dipilih user (dari URL.createObjectURL)
// - path relatif → foto existing dari BE saat edit, perlu prefix /storage/
const resolvedPhotoPreview = computed(() => storageUrl(props.photoPreview));

// ── Item types ────────────────────────────────────────────────────────────

const itemTypes: ItemType[] = ["single", "bundle"];

// ── Event handlers ────────────────────────────────────────────────────────

const onUpdateVisible = (value: boolean) => emit("update:visible", value);
const onCancel = () => {
  emit("cancel");
  emit("update:visible", false);
};
const onSubmit = () => emit("submit");
const onOpenBundleComponentModal = (index?: number) =>
  emit("open-bundle-component-modal", index);
const onRemoveBundleComponent = (index: number) =>
  emit("remove-bundle-component", index);
const onPhotoSelected = (event: Event) => emit("photo-selected", event);
const onRemovePhoto = () => emit("remove-photo");
</script>

<template>
  <Dialog
    :visible="props.visible"
    :header="props.dialogTitle"
    modal
    class="w-full md:w-2/3"
    @update:visible="onUpdateVisible"
  >
    <div class="space-y-4">
      <!-- Nama Alat -->
      <div>
        <label for="toolName" class="block text-sm font-medium mb-1">
          Nama Alat <span class="text-red-500">*</span>
        </label>
        <InputText
          id="toolName"
          v-model="localName"
          class="w-full"
          :disabled="props.loading"
          placeholder="Contoh: Gerinda Tangan"
        />
      </div>

      <!-- Kategori -->
      <div>
        <label for="toolCategory" class="block text-sm font-medium mb-1">
          Kategori <span class="text-red-500">*</span>
        </label>
        <!--
                    FIX: v-model pakai localCategoryId (local ref), bukan props.form.category_id.
                    Watch di atas memastikan localCategoryId sudah berisi nilai yang benar
                    saat categories selesai di-load, sehingga Select bisa mencocokkan option.
                -->
        <Select
          id="toolCategory"
          v-model="localCategoryId"
          :options="categoryOptions"
          option-label="label"
          option-value="value"
          placeholder="Pilih kategori"
          class="w-full"
          :disabled="props.loading"
          :loading="categoryStore.loading"
        />
      </div>

      <!-- Tipe -->
      <div>
        <label for="itemType" class="block text-sm font-medium mb-1">
          Tipe <span class="text-red-500">*</span>
        </label>
        <Select
          id="itemType"
          :model-value="localItemType"
          :options="itemTypes"
          placeholder="Pilih tipe"
          class="w-full"
          :disabled="props.loading || props.isEditMode"
          @update:modelValue="onItemTypeChange"
        />
        <small v-if="props.isEditMode" class="text-surface-400">
          Tipe tidak dapat diubah setelah dibuat.
        </small>
      </div>

      <!-- Harga -->
      <div v-if="localItemType === 'single' || localItemType === 'bundle'">
        <label for="toolPrice" class="block text-sm font-medium mb-1">
          {{ localItemType === "bundle" ? "Harga Bundle" : "Harga Tool" }}
          <span class="text-red-500">*</span>
        </label>
        <InputNumber
          id="toolPrice"
          v-model="localPrice"
          mode="currency"
          currency="IDR"
          locale="id-ID"
          class="w-full"
          :disabled="props.loading"
        />
      </div>

      <!-- Min Credit Score -->
      <div>
        <label for="minCreditScore" class="block text-sm font-medium mb-1">
          Minimum Credit Score
        </label>
        <InputNumber
          id="minCreditScore"
          v-model="localMinCredit"
          class="w-full"
          :min="0"
          :max="100"
          :disabled="props.loading"
        />
      </div>

      <!-- Kode Slug -->
      <div>
        <label for="codeSlug" class="block text-sm font-medium mb-1">
          Kode Slug <span class="text-red-500">*</span>
        </label>
        <InputGroup>
          <InputGroupAddon v-if="props.isBundle">SET-</InputGroupAddon>
          <InputText
            id="codeSlug"
            v-model="localCodeSlug"
            class="w-full"
            :disabled="props.loading"
            placeholder="Contoh: GERINDA"
            style="text-transform: uppercase"
          />
        </InputGroup>
        <small class="text-surface-400">
          Akan otomatis diubah ke huruf kapital.
          {{ props.isBundle ? "Prefix SET- ditambahkan otomatis." : "" }}
        </small>
      </div>

      <!-- Foto Alat -->
      <div>
        <label class="block text-sm font-medium mb-1">Foto Alat</label>
        <div class="space-y-3">
          <!--
                        FIX: resolvedPhotoPreview menangani dua skenario:
                        1. Edit mode: photo_path dari BE (e.g. "tools/foto.jpg")
                           → dikonversi ke "/storage/tools/foto.jpg"
                        2. File baru dipilih: blob: URL dari URL.createObjectURL()
                           → langsung dipakai tanpa prefix
                    -->
          <div v-if="resolvedPhotoPreview" class="flex items-start gap-3">
            <img
              :src="resolvedPhotoPreview"
              alt="Preview foto alat"
              class="w-32 h-32 object-cover rounded-lg border border-surface-200"
            />
            <Button
              icon="pi pi-times"
              severity="danger"
              text
              rounded
              size="small"
              :disabled="props.loading"
              title="Hapus foto"
              @click="onRemovePhoto"
            />
          </div>

          <!-- Placeholder jika tidak ada foto -->
          <div
            v-else
            class="w-32 h-32 rounded-lg border-2 border-dashed border-surface-300 flex flex-col items-center justify-center gap-1 text-surface-400"
          >
            <i class="pi pi-image text-2xl" />
            <span class="text-xs">Belum ada foto</span>
          </div>

          <input
            id="toolPhoto"
            type="file"
            accept="image/*"
            :disabled="props.loading"
            @change="onPhotoSelected"
          />
          <small class="text-surface-400 block">
            Format: JPG, PNG, WebP. Maks 2MB.
          </small>
        </div>
      </div>

      <!-- Deskripsi -->
      <div>
        <label for="toolDescription" class="block text-sm font-medium mb-1">
          Deskripsi
        </label>
        <Textarea
          id="toolDescription"
          v-model="localDescription"
          rows="3"
          class="w-full"
          :disabled="props.loading"
          placeholder="Deskripsi singkat alat..."
        />
      </div>

      <!-- Bundle Components Section -->
      <template v-if="props.isBundle">
        <Divider align="left">
          <span class="text-sm font-medium">
            Komponen Bundle ({{ props.bundleComponentsCount }})
          </span>
        </Divider>

        <div>
          <div class="flex justify-between items-center mb-3">
            <span class="text-sm text-surface-500">
              Daftar alat yang termasuk dalam bundle ini
            </span>
            <Button
              label="Tambah Komponen"
              icon="pi pi-plus"
              size="small"
              :disabled="props.loading"
              @click="onOpenBundleComponentModal()"
            />
          </div>

          <DataTable
            :value="props.form.bundle_components || []"
            class="text-sm"
            responsiveLayout="scroll"
          >
            <Column field="name" header="Nama Komponen" />
            <Column header="Qty" style="width: 5rem" body-class="text-center">
              <template #body="slotProps">
                <Tag
                  :value="String(slotProps.data.qty)"
                  severity="secondary"
                  rounded
                />
              </template>
            </Column>
            <Column header="Harga" style="width: 10rem">
              <template #body="slotProps">
                {{
                  new Intl.NumberFormat("id-ID", {
                    style: "currency",
                    currency: "IDR",
                    minimumFractionDigits: 0,
                  }).format(slotProps.data.price)
                }}
              </template>
            </Column>
            <Column header="Aksi" style="width: 7rem" body-class="text-center">
              <template #body="slotProps">
                <Button
                  icon="pi pi-pencil"
                  text
                  rounded
                  size="small"
                  :disabled="props.loading"
                  @click="onOpenBundleComponentModal(slotProps.index)"
                />
                <Button
                  icon="pi pi-trash"
                  text
                  rounded
                  severity="danger"
                  size="small"
                  :disabled="props.loading"
                  @click="onRemoveBundleComponent(slotProps.index)"
                />
              </template>
            </Column>

            <template #empty>
              <div class="text-center text-surface-400 py-4 text-sm">
                Belum ada komponen. Klik "Tambah Komponen" untuk menambahkan.
              </div>
            </template>
          </DataTable>
        </div>
      </template>
    </div>

    <!-- Footer -->
    <template #footer>
      <Button
        label="Batal"
        severity="secondary"
        :disabled="props.loading"
        @click="onCancel"
      />
      <Button
        :label="props.submitButtonLabel"
        :loading="props.loading"
        @click="onSubmit"
      />
    </template>
  </Dialog>
</template>
