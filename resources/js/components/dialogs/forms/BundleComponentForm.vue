<script setup lang="ts">
import { ref, watch } from "vue";
import type { BundleComponentPayload } from "@/types/tool";

interface Props {
  visible: boolean;
  loading?: boolean;
  modelValue: BundleComponentPayload;
  isEdit?: boolean;
}

interface Emits {
  (e: "update:visible", val: boolean): void;
  (e: "update:modelValue", val: BundleComponentPayload): void;
  (e: "save"): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const localForm = ref<BundleComponentPayload>({
  name: "",
  price: 0,
  qty: 1,
  description: null,
  photo_path: null,
});

// sync dari parent ke local
watch(
  () => props.modelValue,
  (val) => {
    localForm.value = { ...val };
  },
  { immediate: true },
);

function updateField<K extends keyof BundleComponentPayload>(
  key: K,
  value: BundleComponentPayload[K],
) {
  localForm.value[key] = value;
  emit("update:modelValue", localForm.value);
}

function close() {
  emit("update:visible", false);
}

function save() {
  emit("save");
  close();
}
</script>

<template>
  <Dialog
    :visible="props.visible"
    modal
    header="Komponen Bundle"
    class="w-full md:w-1/2"
    @update:visible="close"
  >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-1">
          <label class="text-sm font-medium">Nama</label>
          <InputText
            class="w-full"
            :model-value="localForm.name"
            @update:modelValue="(val) => updateField('name', val)"
          />
        </div>
      
        <div class="space-y-1">
          <label class="text-sm font-medium">Qty</label>
          <InputNumber
            class="w-full"
            :model-value="localForm.qty"
            :min="1"
            @update:modelValue="(val) => updateField('qty', val)"
          />
        </div>
      
        <div class="md:col-span-2 space-y-1">
          <label class="text-sm font-medium">Harga</label>
          <InputNumber
            class="w-full"
            mode="currency"
            currency="IDR"
            locale="id-ID"
            :model-value="localForm.price"
            @update:modelValue="(val) => updateField('price', val)"
          />
        </div>
      
        <div class="md:col-span-2 space-y-1">
          <label class="text-sm font-medium">Deskripsi</label>
          <Textarea
            class="w-full"
            rows="3"
            :model-value="localForm.description"
            @update:modelValue="(val) => updateField('description', val)"
          />
        </div>
      </div>

    <template #footer>
      <Button label="Batal" severity="secondary" @click="close" />
      <Button label="Simpan" @click="save" :loading="props.loading" />
    </template>
  </Dialog>
</template>
