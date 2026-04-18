<script setup lang="ts">
import { computed, watch, ref } from "vue";
import type { ItemType, BundleComponentPayload } from "@/types/tool";
import type { ToolFormData } from "@/pages/admin/tools/composable/useToolManagement";
import { useCategoryStore } from "@/stores/category";


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
  (e: "photo-selected", file: File): void;
  (e: "remove-photo"): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  isEditMode: false,
  photoPreview: null,
});

const emit = defineEmits<Emits>();


const categoryStore = useCategoryStore();

const categoryOptions = computed(() =>
  categoryStore.categories.map((c) => ({ label: c.name, value: c.id })),
);

if (!categoryStore.hasCategories) {
  categoryStore.fetchCategories();
}


const localName = ref(props.form.name);
const localCategoryId = ref<number | null>(props.form.category_id);
const localItemType = ref(props.form.item_type);
const localPrice = ref(props.form.price);
const localMinCredit = ref(props.form.min_credit_score);
const localCodeSlug = ref(props.form.code_slug);
const localDescription = ref(props.form.description);

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

function onItemTypeChange(value: ItemType) {
  localItemType.value = value;
  emit("item-type-change", value);
}

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


const resolvedPhotoPreview = computed(() => {
  return localPhotoPreview.value || storageUrl(props.photoPreview);
});

const itemTypes: ItemType[] = ["single", "bundle"];


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

const localPhotoPreview = ref<string | null>(null);

function onFileSelect(event: any) {
  const file = event.files?.[0];
  if (!file) return;

  if (!file.type.startsWith("image/")) return;
  if (file.size > 2_000_000) return;

  localPhotoPreview.value = URL.createObjectURL(file);

  emit("photo-selected", file);
}
function onRemovePhoto() {
  localPhotoPreview.value = null;
  emit("remove-photo");
}

watch(
  () => props.visible,
  (val) => {
    if (val) {
      localPhotoPreview.value = null;
    }
  }
);
</script>

<template>
  <Dialog
    :visible="props.visible"
    :header="props.dialogTitle"
    modal
    class="w-full md:w-2/3"
    @update:visible="onUpdateVisible"
  >
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-1">
        <label class="text-sm font-medium">
          Nama Alat <span class="text-red-500">*</span>
        </label>
        <InputText
          v-model="localName"
          class="w-full"
          :disabled="props.loading"
          placeholder="Contoh: Gerinda Tangan"
        />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium">
          Kategori <span class="text-red-500">*</span>
        </label>
        <Select
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

      <div class="space-y-1">
        <label class="text-sm font-medium">
          Tipe <span class="text-red-500">*</span>
        </label>
        <Select
          :model-value="localItemType"
          :options="itemTypes"
          class="w-full"
          :disabled="props.loading || props.isEditMode"
          @update:modelValue="onItemTypeChange"
        />
        <small v-if="props.isEditMode" class="text-xs text-surface-400">
          Tidak bisa diubah
        </small>
      </div>

      <div v-if="localItemType !== 'bundle_tool'" class="space-y-1">
        <label class="text-sm font-medium">
          Harga <span class="text-red-500">*</span>
        </label>
        <InputNumber
          v-model="localPrice"
          mode="currency"
          currency="IDR"
          locale="id-ID"
          class="w-full"
          :disabled="props.loading"
        />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium">Min Credit Score</label>
        <InputNumber
          v-model="localMinCredit"
          class="w-full"
          :min="0"
          :max="100"
          :disabled="props.loading"
        />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium">
          Code Slug <span class="text-red-500">*</span>
        </label>
        <InputGroup>
          <InputGroupAddon v-if="props.isBundle">SET-</InputGroupAddon>
          <InputText
            v-model="localCodeSlug"
            class="w-full uppercase"
            :disabled="props.loading"
          />
        </InputGroup>
      </div>
      <div class="md:col-span-2 space-y-1">
        <label class="text-sm font-medium">Deskripsi</label>
        <Textarea
          v-model="localDescription"
          rows="3"
          class="w-full"
          :disabled="props.loading"
        />
      </div>
      <div class="md:col-span-2">
        <Divider align="left">
          <span class="text-sm font-medium">Foto</span>
        </Divider>

        <div class="flex items-start gap-4">
          <div>
            <div
              v-if="resolvedPhotoPreview"
              class="relative w-32 h-32 rounded-lg overflow-hidden border"
            >
              <img
                :src="resolvedPhotoPreview"
                class="w-full h-full object-cover"
              />
              <Button
                icon="pi pi-times"
                severity="danger"
                text
                rounded
                size="small"
                class="absolute top-1 right-1"
                @click="onRemovePhoto"
              />
            </div>

            <div
              v-else
              class="w-32 h-32 border-2 border-dashed rounded-lg flex items-center justify-center text-surface-400"
            >
              <i class="pi pi-image text-xl" />
            </div>
          </div>

          <div class="flex flex-col justify-center gap-2">
            <FileUpload
              mode="basic"
              name="photo"
              accept="image/*"
              :maxFileSize="2000000"
              chooseLabel="Pilih Foto"
              class="w-full"
              :disabled="props.loading"
              @select="onFileSelect"
            />
            <small class="text-xs text-surface-400">
              JPG/PNG/WebP • max 2MB
            </small>
          </div>
        </div>
      </div>

      <template v-if="props.isBundle">
        <div class="md:col-span-2 space-y-1">
          <Divider align="left">
            <span class="text-sm font-medium">
              Komponen Bundle ({{ props.bundleComponentsCount }})
            </span>
          </Divider>

          <BundleComponentTable
            :items="props.form.bundle_components || []"
            :loading="props.loading"
            @add="onOpenBundleComponentModal()"
            @edit="onOpenBundleComponentModal"
            @remove="onRemoveBundleComponent"
          />
        </div>
      </template>
    </div>

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
