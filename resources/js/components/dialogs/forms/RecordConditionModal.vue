<script setup lang="ts">
import { ref, computed } from "vue";
import type { RecordConditionPayload } from "@/types/toolunit";

// ── Props & Emits ─────────────────────────────────────────────────────────

interface Props {
  visible: boolean;
  unitCode?: string;
  loading?: boolean;
}

interface Emits {
  (e: "update:visible", value: boolean): void;
  (e: "submit", payload: RecordConditionPayload): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const emit = defineEmits<Emits>();

// ── State ─────────────────────────────────────────────────────────────────

const condition = ref<"good" | "broken" | "maintenance" | null>(null);
const notes = ref("");
const errors = ref<Record<string, string>>({});

// ── Computed ────────────────────────────────────────────────────────────

const isFormValid = computed(() => {
  return condition.value !== null;
});

// ── Methods ───────────────────────────────────────────────────────────────

function validateForm(): boolean {
  errors.value = {};

  if (!condition.value) {
    errors.value.condition = "Kondisi harus dipilih";
  }

  return Object.keys(errors.value).length === 0;
}

function handleSubmit() {
  if (!validateForm()) {
    return;
  }

  const payload: RecordConditionPayload = {
    condition: condition.value,
    notes: notes.value || undefined,
  };

  emit("submit", payload);
}

function handleClose() {
  emit("update:visible", false);
}

function resetForm() {
  condition.value = null;
  notes.value = "";
  errors.value = {};
}

// Handle dialog show
function onDialogShow() {
  resetForm();
}

const conditionOptions = [
  { label: "Baik", value: "good" },
  { label: "Rusak", value: "broken" },
  { label: "Maintenance", value: "maintenance" },
];

const conditionDescriptions: Record<string, string> = {
  good: "Unit dalam kondisi baik dan siap digunakan",
  broken: "Unit mengalami kerusakan dan tidak dapat digunakan",
  maintenance: "Unit sedang dalam pemeliharaan/perbaikan",
};

function getConditionIcon(val: string) {
  if (val === 'good') return 'pi pi-check-circle';
  if (val === 'broken') return 'pi pi-times-circle';
  return 'pi pi-cog';
}

function getSeverityClass(val: string) {
  if (val === 'good') return 'bg-green-50 border-green-200 text-green-700';
  if (val === 'broken') return 'bg-red-50 border-red-200 text-red-700';
  return 'bg-orange-50 border-orange-200 text-orange-700';
}
</script>

<template>
  <Dialog
    :visible="visible"
    header="Update Kondisi Unit"
    modal
    :closable="!loading"
    class="w-full sm:w-112.5"
    @update:visible="handleClose"
    @show="onDialogShow"
  >
    <div class="flex flex-col gap-6 py-2">
      <div v-if="unitCode" class="flex items-center justify-between bg-surface-50 dark:bg-surface-900 p-4 rounded-xl border border-surface-200 dark:border-surface-700">
        <div>
          <p class="text-xs text-surface-500 font-medium uppercase tracking-wider">Unit Code</p>
          <p class="text-xl font-mono font-bold text-primary leading-none mt-1">{{ unitCode }}</p>
        </div>
        <i class="pi pi-box text-3xl text-surface-300"></i>
      </div>

      <div class="flex flex-col gap-3">
        <label class="text-sm font-bold flex items-center gap-1">
          Pilih Kondisi Baru <span class="text-red-500">*</span>
        </label>
        <SelectButton
          v-model="condition"
          :options="conditionOptions"
          option-label="label"
          option-value="value"
          aria-labelledby="basic"
          :disabled="loading"
          class="w-full"
        >
          <template #option="slotProps">
            <div class="flex flex-col items-center gap-1 px-2 py-1">
              <i :class="getConditionIcon(slotProps.option.value)" class="text-lg"></i>
              <span class="text-xs font-semibold">{{ slotProps.option.label }}</span>
            </div>
          </template>
        </SelectButton>
        
        <transition name="p-connected-overlay">
          <div v-if="condition" :class="getSeverityClass(condition)" class="p-3 rounded-lg text-xs border">
             <i class="pi pi-info-circle mr-2"></i> {{ conditionDescriptions[condition] }}
          </div>
        </transition>
      </div>

      <div class="flex flex-col gap-2">
        <label for="condition-notes" class="text-sm font-bold">Detail Catatan</label>
        <Textarea
          id="condition-notes"
          v-model="notes"
          placeholder="Contoh: Body lecet, layar berkedip, atau butuh kalibrasi..."
          :disabled="loading"
          rows="3"
          auto-resize
          class="w-full"
        />
        <div class="flex justify-between text-[10px] uppercase font-bold text-surface-400 mt-1">
          <span>Riwayat akan tersimpan otomatis</span>
          <span :class="{'text-red-500': notes.length > 900}">{{ notes.length }} / 1000</span>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-2 w-full">
        <Button label="Batal" severity="secondary" text class="flex-1" :disabled="loading" @click="handleClose" />
        <Button 
          label="Simpan Perubahan" 
          icon="pi pi-save" 
          class="flex-1" 
          :loading="loading" 
          :disabled="!isFormValid || loading" 
          @click="handleSubmit" 
        />
      </div>
    </template>
  </Dialog>
</template>