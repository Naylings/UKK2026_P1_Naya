<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useFormatter } from "@/utils/useFormatter";

const props = defineProps<{
  visible: boolean;
  selectedLoan: any;
  loading: boolean;
}>();

const emit = defineEmits<{
  (e: "update:visible", val: boolean): void;
  (e: "submit", payload: { proof: File | null }): void;
}>();

const { formatDate } = useFormatter();

const modelVisible = computed({
  get: () => props.visible,
  set: (val) => emit("update:visible", val),
});

const proof = ref<File | null>(null);
const previewUrl = ref<string | null>(null);
const showValidation = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileInput = () => {
  fileInput.value?.click();
};

watch(
  () => props.visible,
  (val) => {
    if (!val) {
      proof.value = null;
      previewUrl.value = null;
      showValidation.value = false;
    }
  },
);

const handleFileChange = (e: any) => {
  const file = e.target.files[0];
  if (file) {
    proof.value = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};

const close = () => {
  modelVisible.value = false;
};

const submit = () => {
  if (!proof.value) {
    showValidation.value = true;
    return;
  }

  emit("submit", {
    proof: proof.value,
  });
};
</script>

<template>
  <Dialog
    v-model:visible="modelVisible"
    modal
    header="Form Pengembalian Alat"
    style="width: 35rem"
  >
    <div v-if="selectedLoan" class="space-y-6">
      <!-- INFO PINJAMAN -->
      <div
        class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-4"
      >
        <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
          <i class="pi pi-info-circle text-xl"></i>
        </div>
        <div class="flex-1">
          <h3 class="font-bold text-gray-800">
            {{ selectedLoan.tool?.name }}
          </h3>
          <div class="flex items-center gap-2 mt-1">
            <Tag
              :value="selectedLoan.unit?.code"
              severity="secondary"
              class="font-mono"
            />
          </div>
          <p class="text-xs text-gray-500 mt-2 italic">
            "{{ selectedLoan.purpose }}"
          </p>
        </div>
      </div>

      <!-- UPLOAD BUKTI -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="font-semibold text-gray-700"
            >Foto Bukti Pengembalian <span class="text-red-500">*</span></label
          >
          <small class="text-gray-400">Pastikan alat terlihat jelas</small>
        </div>

        <div
          class="relative border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center transition-all hover:bg-gray-50 cursor-pointer"
          :class="[
            showValidation && !proof
              ? 'border-red-300 bg-red-50'
              : 'border-gray-200',
          ]"
          @click="triggerFileInput"
        >
          <input
            ref="fileInput"
            type="file"
            class="hidden"
            accept="image/*"
            @change="handleFileChange"
          />

          <template v-if="!previewUrl">
            <div class="p-4 bg-gray-100 rounded-full mb-3 text-gray-400">
              <i class="pi pi-camera text-3xl"></i>
            </div>
            <p class="text-sm font-medium text-gray-600">
              Klik untuk upload foto
            </p>
            <p class="text-xs text-gray-400 mt-1">PNG, JPG up to 5MB</p>
          </template>

          <template v-else>
            <img :src="previewUrl" class="max-h-64 rounded-lg shadow-md" />
            <div class="absolute top-2 right-2">
              <Button
                icon="pi pi-refresh"
                severity="secondary"
                rounded
                size="small"
                @click.stop="triggerFileInput"
              />
            </div>
          </template>
        </div>

        <Message
          v-if="showValidation && !proof"
          severity="error"
          variant="simple"
          size="small"
        >
          Anda wajib melampirkan foto bukti pengembalian.
        </Message>
      </div>

      <!-- ALERT INFO -->
      <div
        class="p-3 bg-amber-50 border border-amber-100 rounded-lg flex gap-3 text-amber-700"
      >
        <i class="pi pi-exclamation-circle text-lg shrink-0"></i>
        <p class="text-xs leading-relaxed">
          Setelah submit, alat akan berstatus <b>"Menunggu Verifikasi"</b>.
          Harap serahkan fisik alat kepada petugas lapangan untuk pengecekan
          kondisi.
        </p>
      </div>

      <!-- ACTION -->
      <div class="flex justify-end gap-3 pt-2">
        <Button label="Batal" severity="secondary" outlined @click="close" />

        <Button
          label="Kirim Pengembalian"
          icon="pi pi-check"
          :loading="loading"
          @click="submit"
        />
      </div>
    </div>
  </Dialog>
</template>
