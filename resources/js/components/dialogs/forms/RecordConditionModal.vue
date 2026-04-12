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
</script>

<template>
  <Dialog
    :visible="visible"
    header="Catat Kondisi Unit"
    modal
    :closable="!loading"
    class="w-full sm:w-96"
    @update:visible="handleClose"
    @show="onDialogShow"
  >
    <div class="space-y-4">
      <!-- Unit Code Info -->
      <div
        v-if="unitCode"
        class="bg-blue-50 border border-blue-200 rounded-lg p-3"
      >
        <p class="text-xs text-blue-700 font-semibold">Unit</p>
        <p class="text-lg font-mono font-bold text-blue-900 mt-1">
          {{ unitCode }}
        </p>
      </div>

      <!-- Kondisi (Required) -->
      <div class="field">
        <label for="condition" class="text-sm font-semibold block mb-2">
          Kondisi Unit
          <span class="text-red-500">*</span>
        </label>
        <Select
          id="condition"
          v-model="condition"
          :options="conditionOptions"
          option-label="label"
          option-value="value"
          :disabled="loading"
          class="w-full"
        />
        <div v-if="errors.condition" class="text-red-500 text-xs mt-1">
          {{ errors.condition }}
        </div>
        <p v-if="condition" class="text-xs text-surface-500 mt-2">
          {{ conditionDescriptions[condition] }}
        </p>
      </div>

      <!-- Notes -->
      <div class="field">
        <label for="condition-notes" class="text-sm font-semibold block mb-2">
          Catatan Kondisi (Opsional)
        </label>
        <Textarea
          id="condition-notes"
          v-model="notes"
          placeholder="Jelaskan detail kondisi (barang lecet, baterai habis, dll)"
          :disabled="false"
          rows="4"
          maxlength="1000"
          class="w-full"
        />
        <div class="text-xs text-surface-500 mt-1">
          {{ notes.length }}/1000 karakter
        </div>
        <Message
          severity="info"
          icon="pi pi-info-circle"
          class="text-xs w-full mt-2"
          :closable="false"
        >
          Jelaskan detail kondisi/kerusakan pada unit ini
        </Message>
      </div>

      <!-- Info Message -->
      <Message
        severity="info"
        icon="pi pi-info-circle"
        class="text-xs w-full"
        :closable="false"
      >
        Catatan kondisi ini akan menjadi riwayat unit dan dapat dilihat di tab
        riwayat
      </Message>
    </div>

    <!-- Footer -->
    <template #footer>
      <Button
        label="Batal"
        icon="pi pi-times"
        text
        :disabled="loading"
        @click="handleClose"
      />
      <Button
        label="Catat Kondisi"
        icon="pi pi-check"
        :loading="loading"
        :disabled="!isFormValid || loading"
        @click="handleSubmit"
      />
    </template>
  </Dialog>
</template>
